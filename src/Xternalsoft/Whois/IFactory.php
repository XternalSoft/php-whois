<?php

declare(strict_types=1);

namespace Xternalsoft\Whois;

use Xternalsoft\Whois\Loaders\ILoader;
use Xternalsoft\Whois\Modules\Asn\AsnModule;
use Xternalsoft\Whois\Modules\Tld\TldModule;

interface IFactory
{
    /**
     * @return ILoader
     */
    function createLoader(): ILoader;

    /**
     * @param Whois $ehois
     * @return AsnModule
     */
    function createAsnModule(Whois $ehois): AsnModule;

    /**
     * @param Whois $ehois
     * @return TldModule
     */
    function createTldModule(Whois $ehois): TldModule;
}
