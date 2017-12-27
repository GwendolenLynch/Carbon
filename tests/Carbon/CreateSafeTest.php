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
use Carbon\Exceptions\InvalidDateException;
use Tests\AbstractTestCase;

class CreateSafeTest extends AbstractTestCase
{
    public function testInvalidDateExceptionProperties(): void
    {
        $e = new InvalidDateException('day', 'foo');
        $this->assertSame('day', $e->getField());
        $this->assertSame('foo', $e->getValue());
    }

    public function testCreateSafeThrowsExceptionForSecondLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('second : -1 is not a valid value.');

        Carbon::createSafe(null, null, null, null, null, -1);
    }

    public function testCreateSafeThrowsExceptionForSecondGreaterThan59(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('second : 60 is not a valid value.');

        Carbon::createSafe(null, null, null, null, null, 60);
    }

    public function testCreateSafeThrowsExceptionForMinuteLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('minute : -1 is not a valid value.');

        Carbon::createSafe(null, null, null, null, -1);
    }

    public function testCreateSafeThrowsExceptionForMinuteGreaterThan59(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('minute : 60 is not a valid value.');

        Carbon::createSafe(null, null, null, null, 60, 25);
    }

    public function testCreateSafeThrowsExceptionForHourLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('hour : -6 is not a valid value.');

        Carbon::createSafe(null, null, null, -6);
    }

    public function testCreateSafeThrowsExceptionForHourGreaterThan24(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('hour : 25 is not a valid value.');

        Carbon::createSafe(null, null, null, 25, 16, 15);
    }

    public function testCreateSafeThrowsExceptionForDayLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('day : -5 is not a valid value.');

        Carbon::createSafe(null, null, -5);
    }

    public function testCreateSafeThrowsExceptionForDayGreaterThan31(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('day : 32 is not a valid value.');

        Carbon::createSafe(null, null, 32, 17, 16, 15);
    }

    public function testCreateSafeThrowsExceptionForMonthLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('month : -4 is not a valid value.');

        Carbon::createSafe(null, -4);
    }

    public function testCreateSafeThrowsExceptionForMonthGreaterThan12(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('month : 13 is not a valid value.');

        Carbon::createSafe(null, 13, 5, 17, 16, 15);
    }

    public function testCreateSafeThrowsExceptionForYearLowerThanZero(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('year : -5 is not a valid value.');

        Carbon::createSafe(-5);
    }

    public function testCreateSafeThrowsExceptionForYearGreaterThan12(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('year : 10000 is not a valid value.');

        Carbon::createSafe(10000, 12, 5, 17, 16, 15);
    }

    public function testCreateSafeThrowsExceptionForInvalidDayInShortMonth(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('day : 31 is not a valid value.');

        // 30 days in April
        Carbon::createSafe(2016, 4, 31, 17, 16, 15);
    }

    public function testCreateSafeThrowsExceptionForInvalidDayForFebruaryInLeapYear(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('day : 30 is not a valid value.');

        // 29 days in February for a leap year
        $this->assertTrue(Carbon::create(2016, 2)->isLeapYear());
        Carbon::createSafe(2016, 2, 30, 17, 16, 15);
    }

    public function testCreateSafePassesForFebruaryInLeapYear(): void
    {
        // 29 days in February for a leap year
        Carbon::createSafe(2016, 2, 29, 17, 16, 15);
        // Checking that no exception is thrown
        $this->addToAssertionCount(1);
    }

    public function testCreateSafeThrowsExceptionForInvalidDayForFebruaryInNonLeapYear(): void
    {
        $this->expectException(\Carbon\Exceptions\InvalidDateException::class);
        $this->expectExceptionMessage('day : 29 is not a valid value.');

        // 28 days in February for a non-leap year
        $this->assertFalse(Carbon::create(2015, 2)->isLeapYear());
        Carbon::createSafe(2015, 2, 29, 17, 16, 15);
    }

    public function testCreateSafePassesForFebruaryInNonLeapYear(): void
    {
        // 28 days in February for a non-leap year
        Carbon::createSafe(2015, 2, 28, 17, 16, 15);
        // Checking that no exception is thrown
        $this->addToAssertionCount(1);
    }

    public function testCreateSafePasses(): void
    {
        $sd = Carbon::createSafe(2015, 2, 15, 17, 16, 15);
        $d = Carbon::create(2015, 2, 15, 17, 16, 15);
        $this->assertEquals($d, $sd);
        $this->assertCarbon($sd, 2015, 2, 15, 17, 16, 15);
    }
}
