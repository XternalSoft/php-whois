<?php

declare(strict_types=1);

namespace Xternalsoft\Whois\Loaders;

use Xternalsoft\Whois\Exceptions\ConnectionException;

class FakeSocketLoader extends SocketLoader
{
    public $text = "";
    public $failOnConnect = false;

    public function loadText($whoisHost, $query)
    {
        if ($this->failOnConnect) {
            throw new ConnectionException("Fake connection fault");
        }
        return $this->text;
    }
}
