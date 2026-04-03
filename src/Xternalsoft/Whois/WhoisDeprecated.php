<?php

declare(strict_types=1);

namespace Xternalsoft\Whois;

use Xternalsoft\Whois\Loaders\ILoader;

trait WhoisDeprecated
{
    /**
     * @deprecated will be removed in v4.2
     * @param ILoader $loader
     * @return Whois
     */
    public static function create(?ILoader $loader = null)
    {
        return Factory::get()->createWhois($loader);
    }
}
