<?php

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

class ExpressiveComparisonTest extends AbstractTestCase
{
    public function testEqualToTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertTrue($dt1->equalTo($dt2));
    }

    public function testEqualToFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertFalse($dt1->equalTo($dt2));
    }

    public function testEqualWithTimezoneTrue()
    {
        $dt1 = Carbon::create(2000, 1, 1, 12, 0, 0, 'America/Toronto');
        $dt2 = Carbon::create(2000, 1, 1, 9, 0, 0, 'America/Vancouver');

        $this->assertTrue($dt1->equalTo($dt2));
    }

    public function testEqualWithTimezoneFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $dt2 = Carbon::createFromDate(2000, 1, 1, 'America/Vancouver');

        $this->assertFalse($dt1->equalTo($dt2));
    }

    public function testNotEqualToTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertTrue($dt1->notEqualTo($dt2));
    }

    public function testNotEqualToFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertFalse($dt1->notEqualTo($dt2));
    }

    public function testNotEqualWithTimezone()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1, 'America/Toronto');
        $dt2 = Carbon::createFromDate(2000, 1, 1, 'America/Vancouver');

        $this->assertTrue($dt1->notEqualTo($dt2));
    }

    public function testGreaterThanTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(1999, 12, 31);

        $this->assertTrue($dt1->greaterThan($dt2));
    }

    public function testGreaterThanFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertFalse($dt1->greaterThan($dt2));
    }

    public function testGreaterThanWithTimezoneTrue()
    {
        $dt1 = Carbon::create(2000, 1, 1, 12, 0, 0, 'America/Toronto');
        $dt2 = Carbon::create(2000, 1, 1, 8, 59, 59, 'America/Vancouver');
        $this->assertTrue($dt1->greaterThan($dt2));
    }

    public function testGreaterThanWithTimezoneFalse()
    {
        $dt1 = Carbon::create(2000, 1, 1, 12, 0, 0, 'America/Toronto');
        $dt2 = Carbon::create(2000, 1, 1, 9, 0, 1, 'America/Vancouver');

        $this->assertFalse($dt1->greaterThan($dt2));
    }

    public function testGreaterThanOrEqualTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(1999, 12, 31);

        $this->assertTrue($dt1->greaterThanOrEqualTo($dt2));
    }

    public function testGreaterThanOrEqualTrueEqual()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertTrue($dt1->greaterThanOrEqualTo($dt2));
    }

    public function testGreaterThanOrEqualFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertFalse($dt1->greaterThanOrEqualTo($dt2));
    }

    public function testLessThanTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertTrue($dt1->lessThan($dt2));
    }

    public function testLessThanFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(1999, 12, 31);

        $this->assertFalse($dt1->lessThan($dt2));
    }

    public function testLessThanOrEqualTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 2);

        $this->assertTrue($dt1->lessThanOrEqualTo($dt2));
    }

    public function testLessThanOrEqualTrueEqual()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertTrue($dt1->lessThanOrEqualTo($dt2));
    }

    public function testLessThanOrEqualFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(1999, 12, 31);

        $this->assertFalse($dt1->lessThanOrEqualTo($dt2));
    }

    public function testBetweenEqualTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 31);

        $this->assertTrue(Carbon::createFromDate(2000, 1, 15)->between($dt1, $dt2, true));
    }

    public function testBetweenNotEqualTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 31);

        $this->assertTrue(Carbon::createFromDate(2000, 1, 15)->between($dt1, $dt2, false));
    }

    public function testBetweenEqualFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 31);

        $this->assertFalse(Carbon::createFromDate(1999, 12, 31)->between($dt1, $dt2, true));
    }

    public function testBetweenNotEqualFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 1);
        $dt2 = Carbon::createFromDate(2000, 1, 31);

        $this->assertFalse(Carbon::createFromDate(2000, 1, 1)->between($dt1, $dt2, false));
    }

    public function testBetweenEqualSwitchTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertTrue(Carbon::createFromDate(2000, 1, 15)->between($dt1, $dt2, true));
    }

    public function testBetweenNotEqualSwitchTrue()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertTrue(Carbon::createFromDate(2000, 1, 15)->between($dt1, $dt2, false));
    }

    public function testBetweenEqualSwitchFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertFalse(Carbon::createFromDate(1999, 12, 31)->between($dt1, $dt2, true));
    }

    public function testBetweenNotEqualSwitchFalse()
    {
        $dt1 = Carbon::createFromDate(2000, 1, 31);
        $dt2 = Carbon::createFromDate(2000, 1, 1);

        $this->assertFalse(Carbon::createFromDate(2000, 1, 1)->between($dt1, $dt2, false));
    }

    public function testMinIsFluid()
    {
        $dt = Carbon::now();

        $this->assertInstanceOfCarbon($dt->minimum());
    }

    public function testMinWithNow()
    {
        $dt = Carbon::create(2012, 1, 1, 0, 0, 0)->minimum();

        $this->assertCarbon($dt, 2012, 1, 1, 0, 0, 0);
    }

    public function testMinWithInstance()
    {
        $dt1 = Carbon::create(2013, 12, 31, 23, 59, 59);
        $dt2 = Carbon::create(2012, 1, 1, 0, 0, 0)->minimum($dt1);

        $this->assertCarbon($dt2, 2012, 1, 1, 0, 0, 0);
    }

    public function testMaxIsFluid()
    {
        $dt = Carbon::now();

        $this->assertInstanceOfCarbon($dt->maximum());
    }

    public function testMaxWithNow()
    {
        $dt = Carbon::create(2099, 12, 31, 23, 59, 59)->maximum();

        $this->assertCarbon($dt, 2099, 12, 31, 23, 59, 59);
    }

    public function testMaxWithInstance()
    {
        $dt1 = Carbon::create(2012, 1, 1, 0, 0, 0);
        $dt2 = Carbon::create(2099, 12, 31, 23, 59, 59)->maximum($dt1);

        $this->assertCarbon($dt2, 2099, 12, 31, 23, 59, 59);
    }

    public function testIsBirthday()
    {
        $dt1 = Carbon::createFromDate(1987, 4, 23);
        $dt2 = Carbon::createFromDate(2014, 9, 26);
        $dt3 = Carbon::createFromDate(2014, 4, 23);

        $this->assertFalse($dt2->isBirthday($dt1));
        $this->assertTrue($dt3->isBirthday($dt1));
    }
}
