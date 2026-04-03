<?php

/**
 * WHOIS Server Configuration Updater
 * 
 * This script synchronizes the local TLD WHOIS server configuration with the official 
 * IANA Root Database. It performs the following actions:
 * 1. Downloads the latest TLD list from IANA.
 * 2. Queries whois.iana.org for each TLD to identify the official WHOIS server.
 * 3. Updates existing entries, adds missing TLDs, and removes decommissioned ones.
 * 4. Preserves custom local configurations (like parserType or queryFormat).
 * 
 * Performance & Safety:
 * - Implements a 1.5-second delay between requests to respect IANA rate limits.
 * - Saves progress in a temporary JSON file to allow resuming after interruption.
 * 
 * Usage: php bin/update-whois-servers.php
 */

$configFile = __DIR__ . '/../src/Xternalsoft/Whois/Configs/module.tld.servers.json';
$progressFile = __DIR__ . '/.iana_update_progress.json';

if (!file_exists($configFile)) {
    die("Error: Configuration file not found at $configFile\n");
}

// Load previous state or initialize new session
if (file_exists($progressFile)) {
    echo "Resuming from previous progress...\n";
    $state = json_decode(file_get_contents($progressFile), true);
    $indexed = $state['indexed'];
    $processedZones = $state['processed'];
} else {
    $config = json_decode(file_get_contents($configFile), true);
    $indexed = [];
    foreach ($config as $entry) {
        $zone = $entry['zone'];
        if (!isset($indexed[$zone])) {
            $indexed[$zone] = [];
        }
        $indexed[$zone][] = $entry;
    }
    $processedZones = [];
}

echo "Downloading TLD list from IANA...\n";
$tldsList = @file_get_contents('https://data.iana.org/TLD/tlds-alpha-by-domain.txt');
if (!$tldsList) {
    die("Error: Unable to fetch TLD list from IANA.\n");
}

$tlds = array_filter(explode("\n", $tldsList), function($line) {
    return $line && $line[0] !== '#';
});

$totalTlds = count($tlds);
echo "Processing $totalTlds TLDs...\n";

$updatedCount = 0;
$addedCount = 0;
$removedCount = 0;

$count = 0;
foreach ($tlds as $tld) {
    $count++;
    $tld = strtolower(trim($tld));
    $zone = "." . $tld;
    
    // Skip if already processed in this session
    if (in_array($zone, $processedZones)) {
        continue;
    }

    echo "[$count/$totalTlds] Checking $zone... ";
    
    // Query IANA
    $output = shell_exec("whois -h whois.iana.org " . escapeshellarg($zone));
    
    if (!$output) {
        echo "ERROR (No response)\n";
        continue;
    }

    // Check if TLD is decommissioned
    if (strpos($output, 'status:       FORMER') !== false || strpos($output, 'This query returned 0 objects') !== false) {
        if (isset($indexed[$zone])) {
            unset($indexed[$zone]);
            echo "REMOVED (Decommissioned)\n";
            $removedCount++;
        } else {
            echo "SKIPPED (Does not exist)\n";
        }
    } else {
        $ianaWhois = null;
        // Accurate regex to capture the whois host
        if (preg_match('/^whois:\s+([a-z0-9\.-]+)$/im', $output, $matches)) {
            $ianaWhois = trim($matches[1]);
        }
        
        if ($ianaWhois) {
            if (isset($indexed[$zone])) {
                $changed = false;
                foreach ($indexed[$zone] as &$entry) {
                    if ($entry['host'] !== $ianaWhois) {
                        $entry['host'] = $ianaWhois;
                        $changed = true;
                    }
                }
                if ($changed) {
                    echo "UPDATED -> $ianaWhois\n";
                    $updatedCount++;
                } else {
                    echo "OK (Up to date)\n";
                }
            } else {
                // New TLD found
                $indexed[$zone] = [["zone" => $zone, "host" => $ianaWhois]];
                echo "ADDED -> $ianaWhois\n";
                $addedCount++;
            }
        } else {
            echo "NO WHOIS SERVER DEFINED\n";
        }
    }
    
    // Save progress after each TLD
    $processedZones[] = $zone;
    file_put_contents($progressFile, json_encode([
        'indexed' => $indexed,
        'processed' => $processedZones
    ]));

    // Polite delay to avoid rate limiting
    usleep(1500000); 
}

// Finalize and rebuild sorted config
$newConfig = [];
ksort($indexed);
foreach ($indexed as $zone => $zoneEntries) {
    foreach ($zoneEntries as $entry) {
        $newConfig[] = $entry;
    }
}

file_put_contents($configFile, json_encode($newConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Clean up progress file on success
if (file_exists($progressFile)) {
    unlink($progressFile);
}

echo "\nDone!\n";
echo "Updated: $updatedCount\n";
echo "Added:   $addedCount\n";
echo "Removed: $removedCount\n";
