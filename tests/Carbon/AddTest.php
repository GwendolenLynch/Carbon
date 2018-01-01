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

class AddTest extends AbstractTestCase
{
    public function testAddYearsPositive(): void
    {
        $this->assertSame(1976, Carbon::createFromDate(1975)->addYears(1)->year);
    }

    public function testAddYearsZero(): void
    {
        $this->assertSame(1975, Carbon::createFromDate(1975)->addYears(0)->year);
    }

    public function testAddYearsNegative(): void
    {
        $this->assertSame(1974, Carbon::createFromDate(1975)->addYears(-1)->year);
    }

    public function testAddYear(): void
    {
        $this->assertSame(1976, Carbon::createFromDate(1975)->addYear()->year);
    }

    public function testAddDaysPositive(): void
    {
        $this->assertSame(1, Carbon::createFromDate(1975, 5, 31)->addDays(1)->day);
    }

    public function testAddDaysZero(): void
    {
        $this->assertSame(31, Carbon::createFromDate(1975, 5, 31)->addDays(0)->day);
    }

    public function testAddDaysNegative(): void
    {
        $this->assertSame(30, Carbon::createFromDate(1975, 5, 31)->addDays(-1)->day);
    }

    public function testAddDay(): void
    {
        $this->assertSame(1, Carbon::createFromDate(1975, 5, 31)->addDay()->day);
    }

    public function testAddWeekdaysPositive(): void
    {
        $dt = Carbon::create(2012, 1, 4, 13, 2, 1)->addWeekdays(9);

        $this->assertSame(17, $dt->day);

        // test for https://bugs.php.net/bug.php?id=54909
        $this->assertSame(13, $dt->hour);
        $this->assertSame(2, $dt->minute);
        $this->assertSame(1, $dt->second);
    }

    public function testAddWeekdaysZero(): void
    {
        $this->assertSame(4, Carbon::createFromDate(2012, 1, 4)->addWeekdays(0)->day);
    }

    public function testAddWeekdaysNegative(): void
    {
        $this->assertSame(18, Carbon::createFromDate(2012, 1, 31)->addWeekdays(-9)->day);
    }

    public function testAddWeekday(): void
    {
        $this->assertSame(9, Carbon::createFromDate(2012, 1, 6)->addWeekday()->day);
    }

    public function testAddWeekdayDuringWeekend(): void
    {
        $this->assertSame(9, Carbon::createFromDate(2012, 1, 7)->addWeekday()->day);
    }

    public function testAddWeeksPositive(): void
    {
        $this->assertSame(28, Carbon::createFromDate(1975, 5, 21)->addWeeks(1)->day);
    }

    public function testAddWeeksZero(): void
    {
        $this->assertSame(21, Carbon::createFromDate(1975, 5, 21)->addWeeks(0)->day);
    }

    public function testAddWeeksNegative(): void
    {
        $this->assertSame(14, Carbon::createFromDate(1975, 5, 21)->addWeeks(-1)->day);
    }

    public function testAddWeek(): void
    {
        $this->assertSame(28, Carbon::createFromDate(1975, 5, 21)->addWeek()->day);
    }

    public function testAddHoursPositive(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0)->addHours(1)->hour);
    }

    public function testAddHoursZero(): void
    {
        $this->assertSame(0, Carbon::createFromTime(0)->addHours(0)->hour);
    }

    public function testAddHoursNegative(): void
    {
        $this->assertSame(23, Carbon::createFromTime(0)->addHours(-1)->hour);
    }

    public function testAddHour(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0)->addHour()->hour);
    }

    public function testAddMinutesPositive(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0, 0)->addMinutes(1)->minute);
    }

    public function testAddMinutesZero(): void
    {
        $this->assertSame(0, Carbon::createFromTime(0, 0)->addMinutes(0)->minute);
    }

    public function testAddMinutesNegative(): void
    {
        $this->assertSame(59, Carbon::createFromTime(0, 0)->addMinutes(-1)->minute);
    }

    public function testAddMinute(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0, 0)->addMinute()->minute);
    }

    public function testAddSecondsPositive(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0, 0, 0)->addSeconds(1)->second);
    }

    public function testAddSecondsZero(): void
    {
        $this->assertSame(0, Carbon::createFromTime(0, 0, 0)->addSeconds(0)->second);
    }

    public function testAddSecondsNegative(): void
    {
        $this->assertSame(59, Carbon::createFromTime(0, 0, 0)->addSeconds(-1)->second);
    }

    public function testAddSecond(): void
    {
        $this->assertSame(1, Carbon::createFromTime(0, 0, 0)->addSecond()->second);
    }

    /**
     * Test non plural methods with non default args.
     */
    public function testAddYearPassingArg(): void
    {
        $this->assertSame(1977, Carbon::createFromDate(1975)->addYear(2)->year);
    }

    public function testAddDayPassingArg(): void
    {
        $this->assertSame(12, Carbon::createFromDate(1975, 5, 10)->addDay(2)->day);
    }

    public function testAddHourPassingArg(): void
    {
        $this->assertSame(2, Carbon::createFromTime(0)->addHour(2)->hour);
    }

    public function testAddMinutePassingArg(): void
    {
        $this->assertSame(2, Carbon::createFromTime(0)->addMinute(2)->minute);
    }

    public function testAddSecondPassingArg(): void
    {
        $this->assertSame(2, Carbon::createFromTime(0)->addSecond(2)->second);
    }

    public function testAddQuarter(): void
    {
        $this->assertSame(8, Carbon::createFromDate(1975, 5, 6)->addQuarter()->month);
    }

    public function testAddQuarterNegative(): void
    {
        $this->assertSame(2, Carbon::createFromDate(1975, 5, 6)->addQuarter(-1)->month);
    }

    public function testSubQuarter(): void
    {
        $this->assertSame(2, Carbon::createFromDate(1975, 5, 6)->subQuarter()->month);
    }

    public function testSubQuarterNegative(): void
    {
        $this->assertCarbon(Carbon::createFromDate(1975, 5, 6)->subQuarters(2), 1974, 11, 6);
    }

    public function testAddCentury(): void
    {
        $this->assertSame(2075, Carbon::createFromDate(1975)->addCentury()->year);
        $this->assertSame(2075, Carbon::createFromDate(1975)->addCentury(1)->year);
        $this->assertSame(2175, Carbon::createFromDate(1975)->addCentury(2)->year);
    }

    public function testAddCenturyNegative(): void
    {
        $this->assertSame(1875, Carbon::createFromDate(1975)->addCentury(-1)->year);
        $this->assertSame(1775, Carbon::createFromDate(1975)->addCentury(-2)->year);
    }

    public function testAddCenturies(): void
    {
        $this->assertSame(2075, Carbon::createFromDate(1975)->addCenturies(1)->year);
        $this->assertSame(2175, Carbon::createFromDate(1975)->addCenturies(2)->year);
    }

    public function testAddCenturiesNegative(): void
    {
        $this->assertSame(1875, Carbon::createFromDate(1975)->addCenturies(-1)->year);
        $this->assertSame(1775, Carbon::createFromDate(1975)->addCenturies(-2)->year);
    }

    public function testSubCentury(): void
    {
        $this->assertSame(1875, Carbon::createFromDate(1975)->subCentury()->year);
        $this->assertSame(1875, Carbon::createFromDate(1975)->subCentury(1)->year);
        $this->assertSame(1775, Carbon::createFromDate(1975)->subCentury(2)->year);
    }

    public function testSubCenturyNegative(): void
    {
        $this->assertSame(2075, Carbon::createFromDate(1975)->subCentury(-1)->year);
        $this->assertSame(2175, Carbon::createFromDate(1975)->subCentury(-2)->year);
    }

    public function testSubCenturies(): void
    {
        $this->assertSame(1875, Carbon::createFromDate(1975)->subCenturies(1)->year);
        $this->assertSame(1775, Carbon::createFromDate(1975)->subCenturies(2)->year);
    }

    public function testSubCenturiesNegative(): void
    {
        $this->assertSame(2075, Carbon::createFromDate(1975)->subCenturies(-1)->year);
        $this->assertSame(2175, Carbon::createFromDate(1975)->subCenturies(-2)->year);
    }
}
