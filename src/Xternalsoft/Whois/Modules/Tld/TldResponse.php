<?php

declare(strict_types=1);

namespace Xternalsoft\Whois\Modules\Tld;

use Xternalsoft\Whois\DataObject;

/**
 * @property string $query
 * @property string $text
 * @property string $host
 * @property string $domain
 */
class TldResponse extends DataObject
{
    use TldResponseDeprected;

    /** @var array<string, string> */
    protected $dataDefault = [
        'query' => '',
        'text' => '',
        'host' => '',
        'domain' => '',
    ];
}
