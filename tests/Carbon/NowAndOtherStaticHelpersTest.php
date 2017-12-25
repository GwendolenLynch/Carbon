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
use DateTime;
use DateTimeZone;
use Tests\AbstractTestCase;

class NowAndOtherStaticHelpersTest extends AbstractTestCase
{
    public function testNow(): void
    {
        $dt = Carbon::now();
        $this->assertSame(\time(), $dt->timestamp);
    }

    public function testNowWithTimezone(): void
    {
        $dt = Carbon::now('Europe/London');
        $this->assertSame(\time(), $dt->timestamp);
        $this->assertSame('Europe/London', $dt->tzName);
    }

    public function testToday(): void
    {
        $dt = Carbon::today();
        $this->assertSame(\date('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testTodayWithTimezone(): void
    {
        $dt = Carbon::today('Europe/London');
        $dt2 = new DateTime('now', new DateTimeZone('Europe/London'));
        $this->assertSame($dt2->format('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testTomorrow(): void
    {
        $dt = Carbon::tomorrow();
        $dt2 = new DateTime('tomorrow');
        $this->assertSame($dt2->format('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testTomorrowWithTimezone(): void
    {
        $dt = Carbon::tomorrow('Europe/London');
        $dt2 = new DateTime('tomorrow', new DateTimeZone('Europe/London'));
        $this->assertSame($dt2->format('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testYesterday(): void
    {
        $dt = Carbon::yesterday();
        $dt2 = new DateTime('yesterday');
        $this->assertSame($dt2->format('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testYesterdayWithTimezone(): void
    {
        $dt = Carbon::yesterday('Europe/London');
        $dt2 = new DateTime('yesterday', new DateTimeZone('Europe/London'));
        $this->assertSame($dt2->format('Y-m-d 00:00:00'), $dt->toDateTimeString());
    }

    public function testMinValue(): void
    {
        $this->assertLessThanOrEqual(-2147483647, Carbon::minValue()->getTimestamp());
    }

    public function testMaxValue(): void
    {
        $this->assertGreaterThanOrEqual(2147483647, Carbon::maxValue()->getTimestamp());
    }
}
