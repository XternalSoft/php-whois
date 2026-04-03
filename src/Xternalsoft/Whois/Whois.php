<?php

declare(strict_types=1);

namespace Xternalsoft\Whois;

use Xternalsoft\Whois\Exceptions\ConnectionException;
use Xternalsoft\Whois\Exceptions\ServerMismatchException;
use Xternalsoft\Whois\Exceptions\WhoisException;
use Xternalsoft\Whois\Loaders\ILoader;
use Xternalsoft\Whois\Modules\Asn\AsnInfo;
use Xternalsoft\Whois\Modules\Asn\AsnModule;
use Xternalsoft\Whois\Modules\Asn\AsnResponse;
use Xternalsoft\Whois\Modules\Tld\TldInfo;
use Xternalsoft\Whois\Modules\Tld\TldResponse;
use Xternalsoft\Whois\Modules\Tld\TldModule;

class Whois
{
    use WhoisDeprecated;

    /**
     * @param ILoader $loader
     */
    public function __construct(ILoader $loader)
    {
        $this->loader = $loader;
    }

    /** @var IFactory */
    private $factory;

    /** @var ILoader */
    private $loader;

    /** @var TldModule */
    private $tldModule;

    /** @var AsnModule */
    private $asnModule;

    /**
     * @param IFactory $factory
     * @return $this
     */
    public function setFactory(IFactory $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    /**
     * @return IFactory
     */
    public function getFactory(): IFactory
    {
        return $this->factory ?: Factory::get();
    }

    /**
     * @return ILoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @return TldModule
     */
    public function getTldModule()
    {
        $this->tldModule = $this->tldModule ?: $this->getFactory()->createTldModule($this);
        return $this->tldModule;
    }

    /**
     * @return AsnModule
     */
    public function getAsnModule()
    {
        $this->asnModule = $this->asnModule ?: $this->getFactory()->createAsnModule($this);
        return $this->asnModule;
    }

    /**
     * @param string $domain
     * @return bool
     * @throws ServerMismatchException
     * @throws ConnectionException
     * @throws WhoisException
     */
    public function isDomainAvailable($domain)
    {
        return $this->getTldModule()->isDomainAvailable($domain);
    }

    /**
     * @param string $domain
     * @return TldResponse
     * @throws ServerMismatchException
     * @throws ConnectionException
     * @throws WhoisException
     */
    public function lookupDomain($domain)
    {
        return $this->getTldModule()->lookupDomain($domain);
    }

    /**
     * @param string $domain
     * @return TldInfo
     * @throws ServerMismatchException
     * @throws ConnectionException
     * @throws WhoisException
     */
    public function loadDomainInfo($domain)
    {
        return $this->getTldModule()->loadDomainInfo($domain);
    }

    /**
     * @param string $asn
     * @return AsnResponse
     * @throws ConnectionException
     * @throws WhoisException
     */
    public function lookupAsn($asn)
    {
        return $this->getAsnModule()->lookupAsn($asn);
    }

    /**
     * @param string $asn
     * @return AsnInfo
     * @throws ConnectionException
     * @throws WhoisException
     */
    public function loadAsnInfo($asn)
    {
        return $this->getAsnModule()->loadAsnInfo($asn);
    }
}
