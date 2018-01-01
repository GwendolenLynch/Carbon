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

class GettersTest extends AbstractTestCase
{
    public function testGettersThrowExceptionOnUnknownGetter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Carbon::create(1234, 5, 6, 7, 8, 9)->doesNotExit;
    }

    public function testYearGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(1234, $d->year);
    }

    public function testYearIsoGetter(): void
    {
        $d = Carbon::createFromDate(2012, 12, 31);
        $this->assertSame(2013, $d->yearIso);
    }

    public function testMonthGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(5, $d->month);
    }

    public function testDayGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(6, $d->day);
    }

    public function testHourGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(7, $d->hour);
    }

    public function testMinuteGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(8, $d->minute);
    }

    public function testSecondGetter(): void
    {
        $d = Carbon::create(1234, 5, 6, 7, 8, 9);
        $this->assertSame(9, $d->second);
    }

    public function testMicroGetter(): void
    {
        $micro = 345678;
        $d = Carbon::parse('2014-01-05 12:34:11.'.$micro);
        $this->assertSame($micro, $d->micro);
    }

    public function testMicroGetterWithDefaultNow(): void
    {
        $d = Carbon::now();
        $this->assertSame(0, $d->micro);
    }

    public function testDayOfWeeGetter(): void
    {
        $d = Carbon::create(2012, 5, 7, 7, 8, 9);
        $this->assertSame(Carbon::MONDAY, $d->dayOfWeek);
    }

    public function testDayOfYearGetter(): void
    {
        $d = Carbon::createFromDate(2012, 5, 7);
        $this->assertSame(127, $d->dayOfYear);
    }

    public function testDaysInMonthGetter(): void
    {
        $d = Carbon::createFromDate(2012, 5, 7);
        $this->assertSame(31, $d->daysInMonth);
    }

    public function testTimestampGetter(): void
    {
        $d = Carbon::create();
        $d->setTimezone('GMT');
        $this->assertSame(0, $d->setDateTime(1970, 1, 1, 0, 0, 0)->timestamp);
    }

    public function testGetAge(): void
    {
        $d = Carbon::now();
        $this->assertSame(0, $d->age);
    }

    public function testGetAgeWithRealAge(): void
    {
        $d = Carbon::createFromDate(1975, 5, 21);
        $age = \intval(\substr((string) (\date('Ymd') - \date('Ymd', $d->timestamp)), 0, -4));

        $this->assertSame($age, $d->age);
    }

    public function dataProviderTestQuarter()
    {
        return array(
            array(1, 1),
            array(2, 1),
            array(3, 1),
            array(4, 2),
            array(5, 2),
            array(6, 2),
            array(7, 3),
            array(8, 3),
            array(9, 3),
            array(10, 4),
            array(11, 4),
            array(12, 4),
        );
    }

    /**
     * @dataProvider \Tests\Carbon\GettersTest::dataProviderTestQuarter
     *
     * @param int $month
     * @param int $quarter
     */
    public function testQuarterFirstOfMonth($month, $quarter): void
    {
        $c = Carbon::create(2015, $month, 1)->startOfMonth();
        $this->assertSame($quarter, $c->quarter);
    }

    /**
     * @dataProvider \Tests\Carbon\GettersTest::dataProviderTestQuarter
     *
     * @param int $month
     * @param int $quarter
     */
    public function testQuarterMiddleOfMonth($month, $quarter): void
    {
        $c = Carbon::create(2015, $month, 15, 12, 13, 14);
        $this->assertSame($quarter, $c->quarter);
    }

    /**
     * @dataProvider \Tests\Carbon\GettersTest::dataProviderTestQuarter
     *
     * @param int $month
     * @param int $quarter
     */
    public function testQuarterLastOfMonth($month, $quarter): void
    {
        $c = Carbon::create(2015, $month, 1)->endOfMonth();
        $this->assertSame($quarter, $c->quarter);
    }

    public function testGetLocalTrue(): void
    {
        // Default timezone has been set to America/Toronto in AbstractTestCase.php
        // @see : http://en.wikipedia.org/wiki/List_of_UTC_time_offsets
        $this->assertTrue(Carbon::createFromDate(2012, 1, 1, 'America/Toronto')->local);
        $this->assertTrue(Carbon::createFromDate(2012, 1, 1, 'America/New_York')->local);
    }

    public function testGetLocalFalse(): void
    {
        $this->assertFalse(Carbon::createFromDate(2012, 7, 1, 'UTC')->local);
        $this->assertFalse(Carbon::createFromDate(2012, 7, 1, 'Europe/London')->local);
    }

    public function testGetUtcFalse(): void
    {
        $this->assertFalse(Carbon::createFromDate(2013, 1, 1, 'America/Toronto')->utc);
        $this->assertFalse(Carbon::createFromDate(2013, 1, 1, 'Europe/Paris')->utc);
    }

    public function testGetUtcTrue(): void
    {
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Atlantic/Reykjavik')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Europe/Lisbon')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Africa/Casablanca')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Africa/Dakar')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Europe/Dublin')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'Europe/London')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'UTC')->utc);
        $this->assertTrue(Carbon::createFromDate(2013, 1, 1, 'GMT')->utc);
    }

    public function testGetDstFalse(): void
    {
        $this->assertFalse(Carbon::createFromDate(2012, 1, 1, 'America/Toronto')->dst);
    }

    public function testGetDstTrue(): void
    {
        $this->assertTrue(Carbon::createFromDate(2012, 7, 1, 'America/Toronto')->dst);
    }

    public function testOffsetForTorontoWithDST(): void
    {
        $this->assertSame(-18000, Carbon::createFromDate(2012, 1, 1, 'America/Toronto')->offset);
    }

    public function testOffsetForTorontoNoDST(): void
    {
        $this->assertSame(-14400, Carbon::createFromDate(2012, 6, 1, 'America/Toronto')->offset);
    }

    public function testOffsetForGMT(): void
    {
        $this->assertSame(0, Carbon::createFromDate(2012, 6, 1, 'GMT')->offset);
    }

    public function testOffsetHoursForTorontoWithDST(): void
    {
        $this->assertSame(-5, Carbon::createFromDate(2012, 1, 1, 'America/Toronto')->offsetHours);
    }

    public function testOffsetHoursForTorontoNoDST(): void
    {
        $this->assertSame(-4, Carbon::createFromDate(2012, 6, 1, 'America/Toronto')->offsetHours);
    }

    public function testOffsetHoursForGMT(): void
    {
        $this->assertSame(0, Carbon::createFromDate(2012, 6, 1, 'GMT')->offsetHours);
    }

    public function testIsLeapYearTrue(): void
    {
        $this->assertTrue(Carbon::createFromDate(2012, 1, 1)->isLeapYear());
    }

    public function testIsLeapYearFalse(): void
    {
        $this->assertFalse(Carbon::createFromDate(2011, 1, 1)->isLeapYear());
    }

    public function testIsLongYearTrue(): void
    {
        $this->assertTrue(Carbon::createFromDate(2015, 1, 1)->isLongYear());
    }

    public function testIsLongYearFalse(): void
    {
        $this->assertFalse(Carbon::createFromDate(2016, 1, 1)->isLongYear());
    }

    public function testWeekOfMonth(): void
    {
        $this->assertSame(5, Carbon::createFromDate(2012, 9, 30)->weekOfMonth);
        $this->assertSame(4, Carbon::createFromDate(2012, 9, 28)->weekOfMonth);
        $this->assertSame(3, Carbon::createFromDate(2012, 9, 20)->weekOfMonth);
        $this->assertSame(2, Carbon::createFromDate(2012, 9, 8)->weekOfMonth);
        $this->assertSame(1, Carbon::createFromDate(2012, 9, 1)->weekOfMonth);
    }

    public function testWeekOfYearFirstWeek(): void
    {
        $this->assertSame(52, Carbon::createFromDate(2012, 1, 1)->weekOfYear);
        $this->assertSame(1, Carbon::createFromDate(2012, 1, 2)->weekOfYear);
    }

    public function testWeekOfYearLastWeek(): void
    {
        $this->assertSame(52, Carbon::createFromDate(2012, 12, 30)->weekOfYear);
        $this->assertSame(1, Carbon::createFromDate(2012, 12, 31)->weekOfYear);
    }

    public function testGetTimezone(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $this->assertSame('America/Toronto', $dt->timezone->getName());

        $dt = Carbon::createFromDate(2000, 1, 1, -5);
        $this->assertSame('America/Chicago', $dt->timezone->getName());
    }

    public function testGetTz(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $this->assertSame('America/Toronto', $dt->tz->getName());

        $dt = Carbon::createFromDate(2000, 1, 1, -5);
        $this->assertSame('America/Chicago', $dt->tz->getName());
    }

    public function testGetTimezoneName(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $this->assertSame('America/Toronto', $dt->timezoneName);

        $dt = Carbon::createFromDate(2000, 1, 1, -5);
        $this->assertSame('America/Chicago', $dt->timezoneName);
    }

    public function testGetTzName(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $this->assertSame('America/Toronto', $dt->tzName);

        $dt = Carbon::createFromDate(2000, 1, 1, -5);
        $this->assertSame('America/Chicago', $dt->timezoneName);
    }

    public function testGetDays(): void
    {
        $days = array(
            Carbon::SUNDAY => 'Sunday',
            Carbon::MONDAY => 'Monday',
            Carbon::TUESDAY => 'Tuesday',
            Carbon::WEDNESDAY => 'Wednesday',
            Carbon::THURSDAY => 'Thursday',
            Carbon::FRIDAY => 'Friday',
            Carbon::SATURDAY => 'Saturday',
        );

        $this->assertSame($days, Carbon::getDays());
    }
}
