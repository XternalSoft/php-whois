<?php

declare(strict_types=1);

namespace Xternalsoft\Whois\Loaders;

use Xternalsoft\Whois\Exceptions\ConnectionException;
use Xternalsoft\Whois\Exceptions\WhoisException;

interface ILoader
{
    /**
     * @param string $whoisHost
     * @param string $query
     * @return string
     * @throws ConnectionException
     * @throws WhoisException
     */
    function loadText($whoisHost, $query);
}