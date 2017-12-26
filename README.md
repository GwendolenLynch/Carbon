# Carbon

[![Latest Stable Version](https://poser.pugx.org/carbondate/carbon/v/stable.png)](https://packagist.org/packages/carbondate/carbon)
[![Total Downloads](https://poser.pugx.org/carbondate/carbon/downloads.png)](https://packagist.org/packages/carbondate/carbon)
[![Build Status](https://travis-ci.org/CarbonDate/Carbon.svg?branch=master)](https://travis-ci.org/CarbonDate/Carbon)
[![StyleCI](https://styleci.io/repos/5724990/shield?style=flat)](https://styleci.io/repos/5724990)
[![codecov](https://codecov.io/gh/CarbonDate/Carbon/branch/master/graph/badge.svg)](https://codecov.io/gh/CarbonDate/Carbon)
[![PHP-Eye](https://php-eye.com/badge/carbondate/carbon/tested.svg?style=flat)](https://php-eye.com/package/carbondate/carbon)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

A simple PHP API extension for DateTime. [https://carbondate.github.io](https://carbondate.github.io)

**NOTE:** This is an (currently) unofficial continuation of [Brian Nesbitt's](http://nesbot.com) work.

```php
use Carbon\Carbon;

printf("Right now is %s", Carbon::now()->toDateTimeString());
printf("Right now in Vancouver is %s", Carbon::now('America/Vancouver'));  //implicit __toString()
$tomorrow = Carbon::now()->addDay();
$lastWeek = Carbon::now()->subWeek();
$nextSummerOlympics = Carbon::createFromDate(2012)->addYears(4);

$officialDate = Carbon::now()->toRfc2822String();

$howOldAmI = Carbon::createFromDate(1975, 5, 21)->age;

$noonTodayLondonTime = Carbon::createFromTime(12, 0, 0, 'Europe/London');

$worldWillEnd = Carbon::createFromDate(2012, 12, 21, 'GMT');

// Don't really want to die so mock now
Carbon::setTestNow(Carbon::createFromDate(2000, 1, 1));

// comparisons are always done in UTC
if (Carbon::now()->gte($worldWillEnd)) {
    die();
}

// Phew! Return to normal behaviour
Carbon::setTestNow();

if (Carbon::now()->isWeekend()) {
    echo 'Party!';
}
echo Carbon::now()->subMinutes(2)->diffForHumans(); // '2 minutes ago'

// ... but also does 'from now', 'after' and 'before'
// rolling up to seconds, minutes, hours, days, months, years

$daysSinceEpoch = Carbon::createFromTimestamp(0)->diffInDays();
```

## Installation

### With Composer

```
$ composer require carbondate/carbon
```

```json
{
    "require": {
        "carbondate/carbon": "^1.0"
    }
}
```

```php
<?php
require 'vendor/autoload.php';

use Carbon\Carbon;

printf("Now: %s", Carbon::now());
```

<a name="install-nocomposer"/>

### Without Composer

Why are you not using [Composer](http://getcomposer.org/)? Download [Carbon.php](https://github.com/CarbonDate/Carbon/blob/master/src/Carbon/Carbon.php) from the repo and save the file into your project path somewhere.

```php
<?php
require 'path/to/Carbon.php';

use Carbon\Carbon;

printf("Now: %s", Carbon::now());
```

## Docs

[https://carbondate.github.io/docs](https://carbondate.github.io/docs)
