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
use DateTimeZone;
use Tests\AbstractTestCase;

class CreateTest extends AbstractTestCase
{
    public function testCreateReturnsDatingInstance(): void
    {
        $d = Carbon::create();
        $this->assertInstanceOfCarbon($d);
    }

    public function testCreateWithDefaults(): void
    {
        $d = Carbon::create();
        $this->assertSame($d->getTimestamp(), Carbon::now()->getTimestamp());
    }

    public function testCreateWithYear(): void
    {
        $d = Carbon::create(2012);
        $this->assertSame(2012, $d->year);
    }

    public function testCreateHandlesNegativeYear(): void
    {
        $c = Carbon::create(-1, 10, 12, 1, 2, 3);
        $this->assertCarbon($c, -1, 10, 12, 1, 2, 3);
    }

    public function testCreateHandlesFiveDigitsPositiveYears(): void
    {
        $c = Carbon::create(999999999, 10, 12, 1, 2, 3);
        $this->assertCarbon($c, 999999999, 10, 12, 1, 2, 3);
    }

    public function testCreateHandlesFiveDigitsNegativeYears(): void
    {
        $c = Carbon::create(-999999999, 10, 12, 1, 2, 3);
        $this->assertCarbon($c, -999999999, 10, 12, 1, 2, 3);
    }

    public function testCreateWithMonth(): void
    {
        $d = Carbon::create(null, 3);
        $this->assertSame(3, $d->month);
    }

    public function testCreateWithInvalidMonth(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(null, -5);
    }

    public function testCreateMonthWraps(): void
    {
        $d = Carbon::create(2011, 0, 1, 0, 0, 0);
        $this->assertCarbon($d, 2010, 12, 1, 0, 0, 0);
    }

    public function testCreateWithDay(): void
    {
        $d = Carbon::create(null, null, 21);
        $this->assertSame(21, $d->day);
    }

    public function testCreateWithInvalidDay(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(null, null, -4);
    }

    public function testCreateDayWraps(): void
    {
        $d = Carbon::create(2011, 1, 40, 0, 0, 0);
        $this->assertCarbon($d, 2011, 2, 9, 0, 0, 0);
    }

    public function testCreateWithHourAndDefaultMinSecToZero(): void
    {
        $d = Carbon::create(null, null, null, 14);
        $this->assertSame(14, $d->hour);
        $this->assertSame(0, $d->minute);
        $this->assertSame(0, $d->second);
    }

    public function testCreateWithInvalidHour(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(null, null, null, -1);
    }

    public function testCreateHourWraps(): void
    {
        $d = Carbon::create(2011, 1, 1, 24, 0, 0);
        $this->assertCarbon($d, 2011, 1, 2, 0, 0, 0);
    }

    public function testCreateWithMinute(): void
    {
        $d = Carbon::create(null, null, null, null, 58);
        $this->assertSame(58, $d->minute);
    }

    public function testCreateWithInvalidMinute(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(2011, 1, 1, 0, -2, 0);
    }

    public function testCreateMinuteWraps(): void
    {
        $d = Carbon::create(2011, 1, 1, 0, 62, 0);
        $this->assertCarbon($d, 2011, 1, 1, 1, 2, 0);
    }

    public function testCreateWithSecond(): void
    {
        $d = Carbon::create(null, null, null, null, null, 59);
        $this->assertSame(59, $d->second);
    }

    public function testCreateWithInvalidSecond(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(null, null, null, null, null, -2);
    }

    public function testCreateSecondsWrap(): void
    {
        $d = Carbon::create(2012, 1, 1, 0, 0, 61);
        $this->assertCarbon($d, 2012, 1, 1, 0, 1, 1);
    }

    public function testCreateWithDateTimeZone(): void
    {
        $d = Carbon::create(2012, 1, 1, 0, 0, 0, new DateTimeZone('Europe/London'));
        $this->assertCarbon($d, 2012, 1, 1, 0, 0, 0);
        $this->assertSame('Europe/London', $d->tzName);
    }

    public function testCreateWithTimeZoneString(): void
    {
        $d = Carbon::create(2012, 1, 1, 0, 0, 0, 'Europe/London');
        $this->assertCarbon($d, 2012, 1, 1, 0, 0, 0);
        $this->assertSame('Europe/London', $d->tzName);
    }

    public function testCreateWithInvalidTimezoneOffset(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::createFromDate(2000, 1, 1, -28236);
    }

    public function testCreateWithValidTimezoneOffset(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1, -4);
        $this->assertSame('America/New_York', $dt->tzName);
    }
}
