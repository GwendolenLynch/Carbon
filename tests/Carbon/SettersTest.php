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

class SettersTest extends AbstractTestCase
{
    public function testYearSetter(): void
    {
        $d = Carbon::now();
        $d->year = 1995;
        $this->assertSame(1995, $d->year);
    }

    public function testMonthSetter(): void
    {
        $d = Carbon::now();
        $d->month = 3;
        $this->assertSame(3, $d->month);
    }

    public function testMonthSetterWithWrap(): void
    {
        $d = Carbon::now();
        $d->month = 13;
        $this->assertSame(1, $d->month);
    }

    public function testDaySetter(): void
    {
        $d = Carbon::now();
        $d->day = 2;
        $this->assertSame(2, $d->day);
    }

    public function testDaySetterWithWrap(): void
    {
        $d = Carbon::createFromDate(2012, 8, 5);
        $d->day = 32;
        $this->assertSame(1, $d->day);
    }

    public function testHourSetter(): void
    {
        $d = Carbon::now();
        $d->hour = 2;
        $this->assertSame(2, $d->hour);
    }

    public function testHourSetterWithWrap(): void
    {
        $d = Carbon::now();
        $d->hour = 25;
        $this->assertSame(1, $d->hour);
    }

    public function testMinuteSetter(): void
    {
        $d = Carbon::now();
        $d->minute = 2;
        $this->assertSame(2, $d->minute);
    }

    public function testMinuteSetterWithWrap(): void
    {
        $d = Carbon::now();
        $d->minute = 65;
        $this->assertSame(5, $d->minute);
    }

    public function testSecondSetter(): void
    {
        $d = Carbon::now();
        $d->second = 2;
        $this->assertSame(2, $d->second);
    }

    public function testTimeSetter(): void
    {
        $d = Carbon::now();
        $d->setTime(1, 1, 1);
        $this->assertSame(1, $d->second);
        $d->setTime(1, 1);
        $this->assertSame(0, $d->second);
    }

    public function testTimeSetterWithChaining(): void
    {
        $d = Carbon::now();
        $d->setTime(2, 2, 2)->setTime(1, 1, 1);
        $this->assertInstanceOfCarbon($d);
        $this->assertSame(1, $d->second);
        $d->setTime(2, 2, 2)->setTime(1, 1);
        $this->assertInstanceOfCarbon($d);
        $this->assertSame(0, $d->second);
    }

    public function testTimeSetterWithZero(): void
    {
        $d = Carbon::now();
        $d->setTime(1, 1);
        $this->assertSame(0, $d->second);
    }

    public function testDateTimeSetter(): void
    {
        $d = Carbon::now();
        $d->setDateTime($d->year, $d->month, $d->day, 1, 1, 1);
        $this->assertSame(1, $d->second);
    }

    public function testDateTimeSetterWithZero(): void
    {
        $d = Carbon::now();
        $d->setDateTime($d->year, $d->month, $d->day, 1, 1);
        $this->assertSame(0, $d->second);
    }

    public function testDateTimeSetterWithChaining(): void
    {
        $d = Carbon::now();
        $d->setDateTime(2013, 9, 24, 17, 4, 29);
        $this->assertInstanceOfCarbon($d);
        $d->setDateTime(2014, 10, 25, 18, 5, 30);
        $this->assertInstanceOfCarbon($d);
        $this->assertCarbon($d, 2014, 10, 25, 18, 5, 30);
    }

    /**
     * @link https://github.com/briannesbitt/Carbon/issues/539
     */
    public function testSetDateAfterStringCreation(): void
    {
        $d = new Carbon('first day of this month');
        $this->assertSame(1, $d->day);
        $d->setDate($d->year, $d->month, 12);
        $this->assertSame(12, $d->day);
    }

    public function testSecondSetterWithWrap(): void
    {
        $d = Carbon::now();
        $d->second = 65;
        $this->assertSame(5, $d->second);
    }

    public function testTimestampSetter(): void
    {
        $d = Carbon::now();
        $d->timestamp = 10;
        $this->assertSame(10, $d->timestamp);

        $d->setTimestamp(11);
        $this->assertSame(11, $d->timestamp);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTimezoneWithInvalidTimezone(): void
    {
        $d = Carbon::now();
        $d->setTimezone('sdf');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown or bad timezone (sdf)
     */
    public function testTimezoneWithInvalidTimezone(): void
    {
        $d = Carbon::now();
        $d->timezone = 'sdf';
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown or bad timezone (sdf)
     */
    public function testTimezoneWithInvalidTimezoneSetter(): void
    {
        $d = Carbon::now();
        $d->timezone('sdf');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown or bad timezone (sdf)
     */
    public function testTzWithInvalidTimezone(): void
    {
        $d = Carbon::now();
        $d->tz = 'sdf';
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown or bad timezone (sdf)
     */
    public function testTzWithInvalidTimezoneSetter(): void
    {
        $d = Carbon::now();
        $d->tz('sdf');
    }

    public function testSetTimezoneUsingString(): void
    {
        $d = Carbon::now();
        $d->setTimezone('America/Toronto');
        $this->assertSame('America/Toronto', $d->tzName);
    }

    public function testTimezoneUsingString(): void
    {
        $d = Carbon::now();
        $d->timezone = 'America/Toronto';
        $this->assertSame('America/Toronto', $d->tzName);

        $d->timezone('America/Vancouver');
        $this->assertSame('America/Vancouver', $d->tzName);
    }

    public function testTzUsingString(): void
    {
        $d = Carbon::now();
        $d->tz = 'America/Toronto';
        $this->assertSame('America/Toronto', $d->tzName);

        $d->tz('America/Vancouver');
        $this->assertSame('America/Vancouver', $d->tzName);
    }

    public function testSetTimezoneUsingDateTimeZone(): void
    {
        $d = Carbon::now();
        $d->setTimezone(new DateTimeZone('America/Toronto'));
        $this->assertSame('America/Toronto', $d->tzName);
    }

    public function testTimezoneUsingDateTimeZone(): void
    {
        $d = Carbon::now();
        $d->timezone = new DateTimeZone('America/Toronto');
        $this->assertSame('America/Toronto', $d->tzName);

        $d->timezone(new DateTimeZone('America/Vancouver'));
        $this->assertSame('America/Vancouver', $d->tzName);
    }

    public function testTzUsingDateTimeZone(): void
    {
        $d = Carbon::now();
        $d->tz = new DateTimeZone('America/Toronto');
        $this->assertSame('America/Toronto', $d->tzName);

        $d->tz(new DateTimeZone('America/Vancouver'));
        $this->assertSame('America/Vancouver', $d->tzName);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSetter(): void
    {
        $d = Carbon::now();
        $d->doesNotExit = 'bb';
    }

    /**
     * @dataProvider \Tests\Carbon\SettersTest::dataProviderTestSetTimeFromTimeString
     *
     * @param int    $hour
     * @param int    $minute
     * @param int    $second
     * @param string $time
     */
    public function testSetTimeFromTimeString($hour, $minute, $second, $time): void
    {
        Carbon::setTestNow(Carbon::create(2016, 2, 12, 1, 2, 3));
        $d = Carbon::now()->setTimeFromTimeString($time);
        $this->assertCarbon($d, 2016, 2, 12, $hour, $minute, $second);
    }

    public function dataProviderTestSetTimeFromTimeString()
    {
        return array(
            array(9, 15, 30, '09:15:30'),
            array(9, 15, 0, '09:15'),
            array(9, 0, 0, '09'),
        );
    }
}
