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

namespace Tests\CarbonInterval;

use Carbon\CarbonInterval;
use Tests\AbstractTestCase;

class GettersTest extends AbstractTestCase
{
    public function testGettersThrowExceptionOnUnknownGetter(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CarbonInterval::year()->sdfsdfss;
    }

    public function testYearsGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(4, $ci->years);
    }

    public function testMonthsGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(5, $ci->months);
    }

    public function testWeeksGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(6, $ci->weeks);
    }

    public function testDayzExcludingWeeksGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(5, $ci->daysExcludeWeeks);
        $this->assertSame(5, $ci->dayzExcludeWeeks);
    }

    public function testDayzGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(6 * 7 + 5, $ci->dayz);
    }

    public function testHoursGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(8, $ci->hours);
    }

    public function testMinutesGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(9, $ci->minutes);
    }

    public function testSecondsGetter(): void
    {
        $ci = CarbonInterval::create(4, 5, 6, 5, 8, 9, 10);
        $this->assertSame(10, $ci->seconds);
    }
}
