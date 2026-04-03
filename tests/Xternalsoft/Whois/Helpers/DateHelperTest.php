<?php

declare(strict_types=1);

namespace Xternalsoft\Whois\Helpers;

use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testParseDate($input, $expectedTimestamp, $message)
    {
        $actual = DateHelper::parseDate($input);
        if ($expectedTimestamp === 0) {
            $this->assertEquals(0, $actual, $message);
        } else {
            $this->assertEquals($expectedTimestamp, $actual, $message);
        }
    }

    public static function dateProvider()
    {
        return [
            ["2001-01-08 21:05:28 CLST", 978987928, "Chilian date with timezone"],
            ["2026-02-04 18:05:02 CLST", 1770228302, "Future chilian date with timezone"],
            ["2019-10-25 12:30:45", 1572006645, "Standard Y-m-d H:i:s"],
            ["25-oct-2019 12:30:45", 1572006645, "d-M-Y H:i:s"],
            ["25.10.2019", 1571961600, "d.m.Y"],
            ["2019.10.25", 1571961600, "Y.m.d"],
            ["20191025", 1571961600, "Ymd"],
            ["25-oct-2019", 1571961600, "d-M-Y"],
            ["25 oct 2019", 1571961600, "d M Y"],
            ["2019-10-25 12:30:45 (GMT+02:00)", 1571999445, "Y-m-d H:i:s (GMT+offset)"],
            ["invalid date", 0, "Invalid date should return 0"],
        ];
    }
}
