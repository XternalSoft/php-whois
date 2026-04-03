<?php

declare(strict_types=1);

use Xternalsoft\Whois\Factory;

// Better autoload detection
$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

$autoloadFile = null;
foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        $autoloadFile = $file;
        break;
    }
}

if (!$autoloadFile) {
    die("Error: vendor/autoload.php not found. Please run 'composer install'.\n");
}

require_once $autoloadFile;

function main(array $argv): void
{
    $action = trim($argv[1] ?? '');
    $args = array_slice($argv, 2);

    if (empty($action)) {
        $action = 'help';
    }

    $actionLower = mb_strtolower(ltrim($action, '-'));
    
    if ($actionLower === 'help' || $actionLower === 'h') {
        help();
        return;
    }

    switch ($action) {
        case 'lookup':
            if (empty($args[0])) {
                echo "Error: domain argument is required for lookup.\n";
                help();
                exit(1);
            }
            lookup($args[0]);
            break;

        case 'info':
            if (empty($args[0])) {
                echo "Error: domain argument is required for info.\n";
                help();
                exit(1);
            }
            $opts = parseOpts(implode(' ', array_slice($args, 1)));
            info($args[0], $opts);
            break;

        default:
            echo "Unknown action: {$action}\n";
            exit(1);
    }
}

function parseOpts(string $str): array
{
    $result = [];
    $rest = trim($str);
    while (preg_match('~--([-_a-z\d]+)(\s+|=)(\'([^\']+)\'|[^-\s]+)~ui', $rest, $m, PREG_OFFSET_CAPTURE)) {
        $result[$m[1][0]] = $m[4][0] ?? $m[3][0];
        $rest = trim(mb_substr($rest, $m[0][1] + mb_strlen($m[0][0])));
    }
    return $result;
}

function help(): void
{
    echo implode("\n", [
        'Welcome to php-whois CLI',
        '',
        '  Syntax:',
        '    php bin/php-whois.php {action} [arg1 arg2 ... argN]',
        '    php bin/php-whois.php help|--help|-h',
        '    php bin/php-whois.php lookup {domain}',
        '    php bin/php-whois.php info {domain} [--parser {type}] [--host {whois}] [--file {path}]',
        '',
        '  Examples',
        '    php bin/php-whois.php lookup google.com',
        '    php bin/php-whois.php info google.com',
        '    php bin/php-whois.php info google.com --parser block',
        '    php bin/php-whois.php info ya.ru --host whois.nic.ru --parser auto',
        '',
    ]) . "\n";
}

function lookup(string $domain): void
{
    echo "Action: lookup\n";
    echo "Domain: '{$domain}'\n\n";

    $whois = Factory::get()->createWhois();
    $result = $whois->lookupDomain($domain);

    var_dump($result);
}

function info(string $domain, array $options = []): void
{
    $options = array_replace([
        'host' => null,
        'parser' => null,
        'file' => null,
    ], $options);

    echo "Action: info\n";
    echo "Domain: '{$domain}'\n";
    echo sprintf("Options: %s\n\n", json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $loader = null;
    if ($options['file']) {
        if (!file_exists($options['file'])) {
            die("Error: File not found: {$options['file']}\n");
        }
        
        // Use a generic mock loader if FakeSocketLoader is not available (likely in non-dev env)
        if (class_exists('\\Xternalsoft\\Whois\\Loaders\\FakeSocketLoader')) {
            $loader = new \Xternalsoft\Whois\Loaders\FakeSocketLoader();
            $loader->text = file_get_contents($options['file']);
        } else {
            // Simple anonymous class implementation of ILoader if available
            echo "Warning: FakeSocketLoader not found, using raw file content bypass.\n";
            // We can't easily mock ILoader here without knowing its interface fully 
            // but we can try to use it if it exists or fallback.
            // Actually, let's stick to the existing logic but add a check.
            die("Error: --file option requires dev dependencies (FakeSocketLoader).\n");
        }
    }

    $factory = Factory::get();
    $whois = $factory->createWhois($loader);
    $tldModule = $factory->createTldModule($whois);
    $servers = $tldModule->matchServers($domain);

    if (!empty($options['host'])) {
        $host = $options['host'];
        $filteredServers = array_filter($servers, function ($server) use ($host) {
            return $server->getHost() === $host;
        });
        
        if (count($filteredServers) === 0 && count($servers) > 0) {
            // If the specific host isn't in matched servers, we take the first matched zone 
            // but override the host
            $baseServer = $servers[0];
            $servers = [new \Xternalsoft\Whois\Modules\Tld\TldServer(
                $baseServer->getZone(),
                $host,
                $baseServer->isCentralized(),
                $baseServer->getParser(),
                $baseServer->getQueryFormat()
            )];
        } else {
            $servers = array_values($filteredServers);
        }
    }

    if (!empty($options['parser'])) {
        try {
            $parser = $factory->createTldParser($options['parser']);
        } catch (\Throwable $e) {
            die("\nError: Cannot create TLD parser with type '{$options['parser']}'\n");
        }
        
        foreach ($servers as $index => $server) {
            $servers[$index] = new \Xternalsoft\Whois\Modules\Tld\TldServer(
                $server->getZone(),
                $server->getHost(),
                $server->isCentralized(),
                $parser,
                $server->getQueryFormat()
            );
        }
    }

    $info = $tldModule->loadDomainData($domain, $servers);

    var_dump($info);
}

main($argv);
