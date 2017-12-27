<?php

declare(strict_types=1);

/*
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Carbon;

use Carbon\Carbon;
use Tests\AbstractTestCase;

class SerializationTest extends AbstractTestCase
{
    /**
     * @var string
     */
    protected $serialized;

    public function setUp(): void
    {
        parent::setUp();

        if (\version_compare(PHP_VERSION, '5.4.0', '<')) {
            $this->serialized = 'O:13:"Carbon\Carbon":3:{s:4:"date";s:19:"2016-02-01 13:20:25";s:13:"timezone_type";i:3;s:8:"timezone";s:15:"America/Toronto";}';
        } else {
            $this->serialized = 'O:13:"Carbon\Carbon":3:{s:4:"date";s:26:"2016-02-01 13:20:25.000000";s:13:"timezone_type";i:3;s:8:"timezone";s:15:"America/Toronto";}';
        }
    }

    public function testSerialize(): void
    {
        $dt = Carbon::create(2016, 2, 1, 13, 20, 25);
        $this->assertSame($this->serialized, $dt->serialize());
        $this->assertSame($this->serialized, \serialize($dt));
    }

    public function testFromUnserialized(): void
    {
        $dt = Carbon::fromSerialized($this->serialized);
        $this->assertCarbon($dt, 2016, 2, 1, 13, 20, 25);

        $dt = \unserialize($this->serialized);
        $this->assertCarbon($dt, 2016, 2, 1, 13, 20, 25);
    }

    public function testSerialization(): void
    {
        $this->assertEquals(Carbon::now(), \unserialize(\serialize(Carbon::now())));
    }

    public function providerTestFromUnserializedWithInvalidValue()
    {
        return array(
            array(null),
            array(true),
            array(false),
            array(123),
            array('foobar'),
        );
    }

    /**
     * @param mixed $value
     *
     * @dataProvider \Tests\Carbon\SerializationTest::providerTestFromUnserializedWithInvalidValue
     */
    public function testFromUnserializedWithInvalidValue($value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid serialized value.');

        Carbon::fromSerialized((string) $value);
    }
}
