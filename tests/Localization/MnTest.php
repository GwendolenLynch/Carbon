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

namespace Tests\Localization;

use Carbon\Carbon;
use Tests\AbstractTestCase;

class MnTest extends AbstractTestCase
{
    public function testDiffForHumansUsingShortUnitsMongolian(): void
    {
        Carbon::setLocale('mn');

        $scope = $this;
        $this->wrapWithNonDstDate(function () use ($scope): void {
            $d = Carbon::now()->subSecond();
            $scope->assertSame('1с-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subSeconds(2);
            $scope->assertSame('2с-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subMinute();
            $scope->assertSame('1м-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subMinutes(2);
            $scope->assertSame('2м-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subHour();
            $scope->assertSame('1ц-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subHours(2);
            $scope->assertSame('2ц-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subDay();
            $scope->assertSame('1 өдөр-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subDays(2);
            $scope->assertSame('2 өдөр-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subWeek();
            $scope->assertSame('1 долоо хоног-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subWeeks(2);
            $scope->assertSame('2 долоо хоног-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subMonth();
            $scope->assertSame('1 сар-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subMonths(2);
            $scope->assertSame('2 сар-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subYear();
            $scope->assertSame('1 жил-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->subYears(2);
            $scope->assertSame('2 жил-н өмнө', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->addSecond();
            $scope->assertSame('одоогоос 1с', $d->diffForHumans(null, false, true));

            $d = Carbon::now()->addSecond();
            $d2 = Carbon::now();
            $scope->assertSame('1с-н дараа', $d->diffForHumans($d2, false, true));
            $scope->assertSame('1с-н өмнө', $d2->diffForHumans($d, false, true));

            $scope->assertSame('1с', $d->diffForHumans($d2, true, true));
            $scope->assertSame('2с', $d2->diffForHumans($d->addSecond(), true, true));
        });
    }
}
