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
use InvalidArgumentException;
use Tests\AbstractTestCase;

class ModifyTest extends AbstractTestCase
{
    public function testSimpleModify(): void
    {
        $a = new Carbon('2014-03-30 00:00:00');
        $b = $a->copy();
        $b->addHours(24);
        $this->assertSame(24, $a->diffInHours($b));
    }

    public function testTimezoneModify(): void
    {
        $a = new Carbon('2014-03-30 00:00:00', 'Europe/London');
        $b = $a->copy();
        $b->addHours(24);
        $this->assertSame(24, $a->diffInHours($b));
    }

    public function providerModifyInvalidParameters()
    {
        yield [null];
        yield [new \DateTime()];
        yield [42];
    }

    /**
     * @dataProvider providerModifyInvalidParameters
     */
    public function testModifyInvalidParameters($modify)
    {
        $this->expectException(InvalidArgumentException::class);

        Carbon::now()->modify($modify);
    }
}
