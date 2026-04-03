<?php

declare(strict_types=1);

namespace Xternalsoft\Whois\Modules\Asn;

use Xternalsoft\Whois\DataObject;

/**
 * @property string $query
 * @property string $text
 * @property string $host
 * @property string $asn
 */
class AsnResponse extends DataObject
{
    use AsnResponseDeprected;

    /** @var array<string, string> */
    protected $dataDefault = [
        'query' => '',
        'text' => '',
        'host' => '',
        'asn' => '',
    ];
}
