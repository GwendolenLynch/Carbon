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
use Carbon\CarbonInterval;
use Closure;
use Tests\AbstractTestCase;

class DiffTest extends AbstractTestCase
{
    protected function wrapWithTestNow(Closure $func, Carbon $dt = null): void
    {
        parent::wrapWithTestNow($func, $dt ?: Carbon::createFromDate(2012, 1, 1));
    }

    public function testDiffInYearsPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInYears($dt->copy()->addYear()));
    }

    public function testDiffInYearsNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-1, $dt->diffInYears($dt->copy()->subYear(), false));
    }

    public function testDiffInYearsNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInYears($dt->copy()->subYear()));
    }

    public function testDiffInYearsVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(1, Carbon::now()->subYear()->diffInYears());
        });
    }

    public function testDiffInYearsEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInYears($dt->copy()->addYear()->addMonths(7)));
    }

    public function testDiffInMonthsPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(13, $dt->diffInMonths($dt->copy()->addYear()->addMonth()));
    }

    public function testDiffInMonthsNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-11, $dt->diffInMonths($dt->copy()->subYear()->addMonth(), false));
    }

    public function testDiffInMonthsNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(11, $dt->diffInMonths($dt->copy()->subYear()->addMonth()));
    }

    public function testDiffInMonthsVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(12, Carbon::now()->subYear()->diffInMonths());
        });
    }

    public function testDiffInMonthsEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInMonths($dt->copy()->addMonth()->addDays(16)));
    }

    public function testDiffInDaysPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(366, $dt->diffInDays($dt->copy()->addYear()));
    }

    public function testDiffInDaysNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-365, $dt->diffInDays($dt->copy()->subYear(), false));
    }

    public function testDiffInDaysNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(365, $dt->diffInDays($dt->copy()->subYear()));
    }

    public function testDiffInDaysVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(7, Carbon::now()->subWeek()->diffInDays());
        });
    }

    public function testDiffInDaysEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInDays($dt->copy()->addDay()->addHours(13)));
    }

    public function testDiffInDaysFilteredPositiveWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(5, $dt->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === 1;
        }, $dt->copy()->endOfMonth()));
    }

    public function testDiffInDaysFilteredPositiveWithSecondObject(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 31);

        $this->assertSame(5, $dt1->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === Carbon::SUNDAY;
        }, $dt2));
    }

    public function testDiffInDaysFilteredNegativeNoSignWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(5, $dt->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === Carbon::SUNDAY;
        }, $dt->copy()->startOfMonth()));
    }

    public function testDiffInDaysFilteredNegativeNoSignWithSecondObject(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertSame(5, $dt1->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === Carbon::SUNDAY;
        }, $dt2));
    }

    public function testDiffInDaysFilteredNegativeWithSignWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(-5, $dt->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === 1;
        }, $dt->copy()->startOfMonth(), false));
    }

    public function testDiffInDaysFilteredNegativeWithSignWithSecondObject(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertSame(-5, $dt1->diffInDaysFiltered(function (Carbon $date) {
            return $date->dayOfWeek === Carbon::SUNDAY;
        }, $dt2, false));
    }

    public function testDiffInHoursFiltered(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31)->endOfDay();
        $dt2 = Carbon::createFromDate(2000, 1, 1)->startOfDay();

        $this->assertSame(31, $dt1->diffInHoursFiltered(function (Carbon $date) {
            return $date->hour === 9;
        }, $dt2));
    }

    public function testDiffInHoursFilteredNegative(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31)->endOfDay();
        $dt2 = Carbon::createFromDate(2000, 1, 1)->startOfDay();

        $this->assertSame(-31, $dt1->diffInHoursFiltered(function (Carbon $date) {
            return $date->hour === 9;
        }, $dt2, false));
    }

    public function testDiffInHoursFilteredWorkHoursPerWeek(): void
    {
        $dt1 = Carbon::createFromDate(2000, 1, 5)->endOfDay();
        $dt2 = Carbon::createFromDate(2000, 1, 1)->startOfDay();

        $this->assertSame(40, $dt1->diffInHoursFiltered(function (Carbon $date) {
            return $date->hour > 8 && $date->hour < 17;
        }, $dt2));
    }

    public function testDiffFilteredUsingMinutesPositiveWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1)->startOfDay();
        $this->assertSame(60, $dt->diffFiltered(CarbonInterval::minute(), function (Carbon $date) {
            return $date->hour === 12;
        }, Carbon::createFromDate(2000, 1, 1)->endOfDay()));
    }

    public function testDiffFilteredPositiveWithSecondObject(): void
    {
        $dt1 = Carbon::create(2000, 1, 1);
        $dt2 = $dt1->copy()->addSeconds(80);

        $this->assertSame(40, $dt1->diffFiltered(CarbonInterval::second(), function (Carbon $date) {
            return $date->second % 2 === 0;
        }, $dt2));
    }

    public function testDiffFilteredNegativeNoSignWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);

        $this->assertSame(2, $dt->diffFiltered(CarbonInterval::days(2), function (Carbon $date) {
            return $date->dayOfWeek === Carbon::SUNDAY;
        }, $dt->copy()->startOfMonth()));
    }

    public function testDiffFilteredNegativeNoSignWithSecondObject(): void
    {
        $dt1 = Carbon::createFromDate(2006, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertSame(7, $dt1->diffFiltered(CarbonInterval::year(), function (Carbon $date) {
            return $date->month === 1;
        }, $dt2));
    }

    public function testDiffFilteredNegativeWithSignWithMutated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(-4, $dt->diffFiltered(CarbonInterval::week(), function (Carbon $date) {
            return $date->month === 12;
        }, $dt->copy()->subMonths(3), false));
    }

    public function testDiffFilteredNegativeWithSignWithSecondObject(): void
    {
        $dt1 = Carbon::createFromDate(2001, 1, 31);
        $dt2 = Carbon::createFromDate(1999, 1, 1);

        $this->assertSame(-12, $dt1->diffFiltered(CarbonInterval::month(), function (Carbon $date) {
            return $date->year === 2000;
        }, $dt2, false));
    }

    public function testBug188DiffWithSameDates(): void
    {
        $start = Carbon::create(2014, 10, 8, 15, 20, 0);
        $end = $start->copy();

        $this->assertSame(0, $start->diffInDays($end));
        $this->assertSame(0, $start->diffInWeekdays($end));
    }

    public function testBug188DiffWithDatesOnlyHoursApart(): void
    {
        $start = Carbon::create(2014, 10, 8, 15, 20, 0);
        $end = $start->copy();

        $this->assertSame(0, $start->diffInDays($end));
        $this->assertSame(0, $start->diffInWeekdays($end));
    }

    public function testBug188DiffWithSameDates1DayApart(): void
    {
        $start = Carbon::create(2014, 10, 8, 15, 20, 0);
        $end = $start->copy()->addDay();

        $this->assertSame(1, $start->diffInDays($end));
        $this->assertSame(1, $start->diffInWeekdays($end));
    }

    public function testBug188DiffWithDatesOnTheWeekend(): void
    {
        $start = Carbon::create(2014, 1, 1, 0, 0, 0);
        $start->next(Carbon::SATURDAY);
        $end = $start->copy()->addDay();

        $this->assertSame(1, $start->diffInDays($end));
        $this->assertSame(0, $start->diffInWeekdays($end));
    }

    public function testDiffInWeekdaysPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(21, $dt->diffInWeekdays($dt->copy()->endOfMonth()));
    }

    public function testDiffInWeekdaysNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(21, $dt->diffInWeekdays($dt->copy()->startOfMonth()));
    }

    public function testDiffInWeekdaysNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(-21, $dt->diffInWeekdays($dt->copy()->startOfMonth(), false));
    }

    public function testDiffInWeekendDaysPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(10, $dt->diffInWeekendDays($dt->copy()->endOfMonth()));
    }

    public function testDiffInWeekendDaysNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(10, $dt->diffInWeekendDays($dt->copy()->startOfMonth()));
    }

    public function testDiffInWeekendDaysNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 31);
        $this->assertSame(-10, $dt->diffInWeekendDays($dt->copy()->startOfMonth(), false));
    }

    public function testDiffInWeeksPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(52, $dt->diffInWeeks($dt->copy()->addYear()));
    }

    public function testDiffInWeeksNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-52, $dt->diffInWeeks($dt->copy()->subYear(), false));
    }

    public function testDiffInWeeksNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(52, $dt->diffInWeeks($dt->copy()->subYear()));
    }

    public function testDiffInWeeksVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(1, Carbon::now()->subWeek()->diffInWeeks());
        });
    }

    public function testDiffInWeeksEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(0, $dt->diffInWeeks($dt->copy()->addWeek()->subDay()));
    }

    public function testDiffInHoursPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(26, $dt->diffInHours($dt->copy()->addDay()->addHours(2)));
    }

    public function testDiffInHoursNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-22, $dt->diffInHours($dt->copy()->subDay()->addHours(2), false));
    }

    public function testDiffInHoursNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(22, $dt->diffInHours($dt->copy()->subDay()->addHours(2)));
    }

    public function testDiffInHoursVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(48, Carbon::now()->subDays(2)->diffInHours());
        }, Carbon::create(2012, 1, 15));
    }

    public function testDiffInHoursEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInHours($dt->copy()->addHour()->addMinutes(31)));
    }

    public function testDiffInMinutesPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(62, $dt->diffInMinutes($dt->copy()->addHour()->addMinutes(2)));
    }

    public function testDiffInMinutesPositiveAlot(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1502, $dt->diffInMinutes($dt->copy()->addHours(25)->addMinutes(2)));
    }

    public function testDiffInMinutesNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-58, $dt->diffInMinutes($dt->copy()->subHour()->addMinutes(2), false));
    }

    public function testDiffInMinutesNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(58, $dt->diffInMinutes($dt->copy()->subHour()->addMinutes(2)));
    }

    public function testDiffInMinutesVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(60, Carbon::now()->subHour()->diffInMinutes());
        });
    }

    public function testDiffInMinutesEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInMinutes($dt->copy()->addMinute()->addSeconds(31)));
    }

    public function testDiffInSecondsPositive(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(62, $dt->diffInSeconds($dt->copy()->addMinute()->addSeconds(2)));
    }

    public function testDiffInSecondsPositiveAlot(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(7202, $dt->diffInSeconds($dt->copy()->addHours(2)->addSeconds(2)));
    }

    public function testDiffInSecondsNegativeWithSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(-58, $dt->diffInSeconds($dt->copy()->subMinute()->addSeconds(2), false));
    }

    public function testDiffInSecondsNegativeNoSign(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(58, $dt->diffInSeconds($dt->copy()->subMinute()->addSeconds(2)));
    }

    public function testDiffInSecondsVsDefaultNow(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame(3600, Carbon::now()->subHour()->diffInSeconds());
        });
    }

    public function testDiffInSecondsEnsureIsTruncated(): void
    {
        $dt = Carbon::createFromDate(2000, 1, 1);
        $this->assertSame(1, $dt->diffInSeconds($dt->copy()->addSeconds(1)));
    }

    public function testDiffInSecondsWithTimezones(): void
    {
        $dtOttawa = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $dtVancouver = Carbon::createFromDate(2000, 1, 1, 'America/Vancouver');
        $this->assertSame(3 * 60 * 60, $dtOttawa->diffInSeconds($dtVancouver));
    }

    public function testDiffInSecondsWithTimezonesAndVsDefault(): void
    {
        $vanNow = Carbon::now('America/Vancouver');
        $hereNow = $vanNow->copy()->setTimezone(Carbon::now()->tz);

        $scope = $this;
        $this->wrapWithTestNow(function () use ($vanNow, $scope): void {
            $scope->assertSame(0, $vanNow->diffInSeconds());
        }, $hereNow);
    }

    public function testDiffForHumansNowAndSecond(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 second ago', Carbon::now()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndSecondWithTimezone(): void
    {
        $vanNow = Carbon::now('America/Vancouver');
        $hereNow = $vanNow->copy()->setTimezone(Carbon::now()->tz);

        $scope = $this;
        $this->wrapWithTestNow(function () use ($vanNow, $scope): void {
            $scope->assertSame('1 second ago', $vanNow->diffForHumans());
        }, $hereNow);
    }

    public function testDiffForHumansNowAndSeconds(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 seconds ago', Carbon::now()->subSeconds(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 seconds ago', Carbon::now()->subSeconds(59)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 minute ago', Carbon::now()->subMinute()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndMinutes(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 minutes ago', Carbon::now()->subMinutes(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 minutes ago', Carbon::now()->subMinutes(59)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 hour ago', Carbon::now()->subHour()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndHours(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 hours ago', Carbon::now()->subHours(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('23 hours ago', Carbon::now()->subHours(23)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 day ago', Carbon::now()->subDay()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndDays(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 days ago', Carbon::now()->subDays(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('6 days ago', Carbon::now()->subDays(6)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 week ago', Carbon::now()->subWeek()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndWeeks(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 weeks ago', Carbon::now()->subWeeks(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('3 weeks ago', Carbon::now()->subWeeks(3)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('4 weeks ago', Carbon::now()->subWeeks(4)->diffForHumans());
            $scope->assertSame('1 month ago', Carbon::now()->subMonth()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndMonths(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 months ago', Carbon::now()->subMonths(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('11 months ago', Carbon::now()->subMonths(11)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 year ago', Carbon::now()->subYear()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndYears(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 years ago', Carbon::now()->subYears(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureSecond(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 second from now', Carbon::now()->addSecond()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureSeconds(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 seconds from now', Carbon::now()->addSeconds(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 seconds from now', Carbon::now()->addSeconds(59)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 minute from now', Carbon::now()->addMinute()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureMinutes(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 minutes from now', Carbon::now()->addMinutes(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 minutes from now', Carbon::now()->addMinutes(59)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 hour from now', Carbon::now()->addHour()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureHours(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 hours from now', Carbon::now()->addHours(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('23 hours from now', Carbon::now()->addHours(23)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 day from now', Carbon::now()->addDay()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureDays(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 days from now', Carbon::now()->addDays(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('6 days from now', Carbon::now()->addDays(6)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 week from now', Carbon::now()->addWeek()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureWeeks(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 weeks from now', Carbon::now()->addWeeks(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('3 weeks from now', Carbon::now()->addWeeks(3)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('4 weeks from now', Carbon::now()->addWeeks(4)->diffForHumans());
            $scope->assertSame('1 month from now', Carbon::now()->addMonth()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureMonths(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 months from now', Carbon::now()->addMonths(2)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndNearlyFutureYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('11 months from now', Carbon::now()->addMonths(11)->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 year from now', Carbon::now()->addYear()->diffForHumans());
        });
    }

    public function testDiffForHumansNowAndFutureYears(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 years from now', Carbon::now()->addYears(2)->diffForHumans());
        });
    }

    public function testDiffForHumansOtherAndSecond(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 second before', Carbon::now()->diffForHumans(Carbon::now()->addSecond()));
        });
    }

    public function testDiffForHumansOtherAndSeconds(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 seconds before', Carbon::now()->diffForHumans(Carbon::now()->addSeconds(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 seconds before', Carbon::now()->diffForHumans(Carbon::now()->addSeconds(59)));
        });
    }

    public function testDiffForHumansOtherAndMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 minute before', Carbon::now()->diffForHumans(Carbon::now()->addMinute()));
        });
    }

    public function testDiffForHumansOtherAndMinutes(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 minutes before', Carbon::now()->diffForHumans(Carbon::now()->addMinutes(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 minutes before', Carbon::now()->diffForHumans(Carbon::now()->addMinutes(59)));
        });
    }

    public function testDiffForHumansOtherAndHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 hour before', Carbon::now()->diffForHumans(Carbon::now()->addHour()));
        });
    }

    public function testDiffForHumansOtherAndHours(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 hours before', Carbon::now()->diffForHumans(Carbon::now()->addHours(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('23 hours before', Carbon::now()->diffForHumans(Carbon::now()->addHours(23)));
        });
    }

    public function testDiffForHumansOtherAndDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 day before', Carbon::now()->diffForHumans(Carbon::now()->addDay()));
        });
    }

    public function testDiffForHumansOtherAndDays(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 days before', Carbon::now()->diffForHumans(Carbon::now()->addDays(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('6 days before', Carbon::now()->diffForHumans(Carbon::now()->addDays(6)));
        });
    }

    public function testDiffForHumansOtherAndWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 week before', Carbon::now()->diffForHumans(Carbon::now()->addWeek()));
        });
    }

    public function testDiffForHumansOtherAndWeeks(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 weeks before', Carbon::now()->diffForHumans(Carbon::now()->addWeeks(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('3 weeks before', Carbon::now()->diffForHumans(Carbon::now()->addWeeks(3)));
        });
    }

    public function testDiffForHumansOtherAndMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('4 weeks before', Carbon::now()->diffForHumans(Carbon::now()->addWeeks(4)));
            $scope->assertSame('1 month before', Carbon::now()->diffForHumans(Carbon::now()->addMonth()));
        });
    }

    public function testDiffForHumansOtherAndMonths(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 months before', Carbon::now()->diffForHumans(Carbon::now()->addMonths(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('11 months before', Carbon::now()->diffForHumans(Carbon::now()->addMonths(11)));
        });
    }

    public function testDiffForHumansOtherAndYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 year before', Carbon::now()->diffForHumans(Carbon::now()->addYear()));
        });
    }

    public function testDiffForHumansOtherAndYears(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 years before', Carbon::now()->diffForHumans(Carbon::now()->addYears(2)));
        });
    }

    public function testDiffForHumansOtherAndFutureSecond(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 second after', Carbon::now()->diffForHumans(Carbon::now()->subSecond()));
        });
    }

    public function testDiffForHumansOtherAndFutureSeconds(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 seconds after', Carbon::now()->diffForHumans(Carbon::now()->subSeconds(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 seconds after', Carbon::now()->diffForHumans(Carbon::now()->subSeconds(59)));
        });
    }

    public function testDiffForHumansOtherAndFutureMinute(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 minute after', Carbon::now()->diffForHumans(Carbon::now()->subMinute()));
        });
    }

    public function testDiffForHumansOtherAndFutureMinutes(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 minutes after', Carbon::now()->diffForHumans(Carbon::now()->subMinutes(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 minutes after', Carbon::now()->diffForHumans(Carbon::now()->subMinutes(59)));
        });
    }

    public function testDiffForHumansOtherAndFutureHour(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 hour after', Carbon::now()->diffForHumans(Carbon::now()->subHour()));
        });
    }

    public function testDiffForHumansOtherAndFutureHours(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 hours after', Carbon::now()->diffForHumans(Carbon::now()->subHours(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('23 hours after', Carbon::now()->diffForHumans(Carbon::now()->subHours(23)));
        });
    }

    public function testDiffForHumansOtherAndFutureDay(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 day after', Carbon::now()->diffForHumans(Carbon::now()->subDay()));
        });
    }

    public function testDiffForHumansOtherAndFutureDays(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 days after', Carbon::now()->diffForHumans(Carbon::now()->subDays(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('6 days after', Carbon::now()->diffForHumans(Carbon::now()->subDays(6)));
        });
    }

    public function testDiffForHumansOtherAndFutureWeek(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 week after', Carbon::now()->diffForHumans(Carbon::now()->subWeek()));
        });
    }

    public function testDiffForHumansOtherAndFutureWeeks(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 weeks after', Carbon::now()->diffForHumans(Carbon::now()->subWeeks(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('3 weeks after', Carbon::now()->diffForHumans(Carbon::now()->subWeeks(3)));
        });
    }

    public function testDiffForHumansOtherAndFutureMonth(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('4 weeks after', Carbon::now()->diffForHumans(Carbon::now()->subWeeks(4)));
            $scope->assertSame('1 month after', Carbon::now()->diffForHumans(Carbon::now()->subMonth()));
        });
    }

    public function testDiffForHumansOtherAndFutureMonths(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 months after', Carbon::now()->diffForHumans(Carbon::now()->subMonths(2)));
        });
    }

    public function testDiffForHumansOtherAndNearlyFutureYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('11 months after', Carbon::now()->diffForHumans(Carbon::now()->subMonths(11)));
        });
    }

    public function testDiffForHumansOtherAndFutureYear(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 year after', Carbon::now()->diffForHumans(Carbon::now()->subYear()));
        });
    }

    public function testDiffForHumansOtherAndFutureYears(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 years after', Carbon::now()->diffForHumans(Carbon::now()->subYears(2)));
        });
    }

    public function testDiffForHumansAbsoluteSeconds(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('59 seconds', Carbon::now()->diffForHumans(Carbon::now()->subSeconds(59), true));
            $scope->assertSame('59 seconds', Carbon::now()->diffForHumans(Carbon::now()->addSeconds(59), true));
        });
    }

    public function testDiffForHumansAbsoluteMinutes(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('30 minutes', Carbon::now()->diffForHumans(Carbon::now()->subMinutes(30), true));
            $scope->assertSame('30 minutes', Carbon::now()->diffForHumans(Carbon::now()->addMinutes(30), true));
        });
    }

    public function testDiffForHumansAbsoluteHours(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('3 hours', Carbon::now()->diffForHumans(Carbon::now()->subHours(3), true));
            $scope->assertSame('3 hours', Carbon::now()->diffForHumans(Carbon::now()->addHours(3), true));
        });
    }

    public function testDiffForHumansAbsoluteDays(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 days', Carbon::now()->diffForHumans(Carbon::now()->subDays(2), true));
            $scope->assertSame('2 days', Carbon::now()->diffForHumans(Carbon::now()->addDays(2), true));
        });
    }

    public function testDiffForHumansAbsoluteWeeks(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 weeks', Carbon::now()->diffForHumans(Carbon::now()->subWeeks(2), true));
            $scope->assertSame('2 weeks', Carbon::now()->diffForHumans(Carbon::now()->addWeeks(2), true));
        });
    }

    public function testDiffForHumansAbsoluteMonths(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('2 months', Carbon::now()->diffForHumans(Carbon::now()->subMonths(2), true));
            $scope->assertSame('2 months', Carbon::now()->diffForHumans(Carbon::now()->addMonths(2), true));
        });
    }

    public function testDiffForHumansAbsoluteYears(): void
    {
        $scope = $this;
        $this->wrapWithTestNow(function () use ($scope): void {
            $scope->assertSame('1 year', Carbon::now()->diffForHumans(Carbon::now()->subYears(1), true));
            $scope->assertSame('1 year', Carbon::now()->diffForHumans(Carbon::now()->addYears(1), true));
        });
    }

    public function testDiffForHumansWithShorterMonthShouldStillBeAMonth(): void
    {
        $feb15 = Carbon::parse('2015-02-15');
        $mar15 = Carbon::parse('2015-03-15');
        $this->assertSame('1 month after', $mar15->diffForHumans($feb15));
    }
}
