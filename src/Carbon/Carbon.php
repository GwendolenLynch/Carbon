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

namespace Carbon;

use Carbon\Exceptions\InvalidDateException;
use Closure;
use DatePeriod;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * A simple API extension for DateTime
 *
 * @property      int $year
 * @property      int $yearIso
 * @property      int $month
 * @property      int $day
 * @property      int $hour
 * @property      int $minute
 * @property      int $second
 * @property      int $timestamp seconds since the Unix Epoch
 * @property      \DateTimeZone $timezone the current timezone
 * @property      \DateTimeZone $tz alias of timezone
 * @property-read int $micro
 * @property-read int $dayOfWeek 0 (for Sunday) through 6 (for Saturday)
 * @property-read int $dayOfYear 0 through 365
 * @property-read int $weekOfMonth 1 through 5
 * @property-read int $weekOfYear ISO-8601 week number of year, weeks starting on Monday
 * @property-read int $daysInMonth number of days in the given month
 * @property-read int $age does a diffInYears() with default parameters
 * @property-read int $quarter the quarter of this instance, 1 - 4
 * @property-read int $offset the timezone offset in seconds from UTC
 * @property-read int $offsetHours the timezone offset in hours from UTC
 * @property-read bool $dst daylight savings time indicator, true if DST, false otherwise
 * @property-read bool $local checks if the timezone is local, true if local, false otherwise
 * @property-read bool $utc checks if the timezone is UTC, true if UTC, false otherwise
 * @property-read string $timezoneName
 * @property-read string $tzName
 */
class Carbon extends DateTime
{
    /**
     * The day constants.
     */
    const SUNDAY = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;

    /**
     * Names of days of the week.
     *
     * @var array
     */
    protected static $days = array(
        self::SUNDAY => 'Sunday',
        self::MONDAY => 'Monday',
        self::TUESDAY => 'Tuesday',
        self::WEDNESDAY => 'Wednesday',
        self::THURSDAY => 'Thursday',
        self::FRIDAY => 'Friday',
        self::SATURDAY => 'Saturday',
    );

    /**
     * Terms used to detect if a time passed is a relative date.
     *
     * This is here for testing purposes.
     *
     * @var array
     */
    protected static $relativeKeywords = array(
        '+',
        '-',
        'ago',
        'first',
        'last',
        'next',
        'this',
        'today',
        'tomorrow',
        'yesterday',
    );

    /**
     * Number of X in Y.
     */
    const YEARS_PER_CENTURY = 100;
    const YEARS_PER_DECADE = 10;
    const MONTHS_PER_YEAR = 12;
    const MONTHS_PER_QUARTER = 3;
    const WEEKS_PER_YEAR = 52;
    const DAYS_PER_WEEK = 7;
    const HOURS_PER_DAY = 24;
    const MINUTES_PER_HOUR = 60;
    const SECONDS_PER_MINUTE = 60;

    /**
     * Default format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    const DEFAULT_TO_STRING_FORMAT = 'Y-m-d H:i:s';

    /**
     * Format to use for __toString method when type juggling occurs.
     *
     * @var string
     */
    protected static $toStringFormat = self::DEFAULT_TO_STRING_FORMAT;

    /**
     * First day of week.
     *
     * @var int
     */
    protected static $weekStartsAt = self::MONDAY;

    /**
     * Last day of week.
     *
     * @var int
     */
    protected static $weekEndsAt = self::SUNDAY;

    /**
     * Days of weekend.
     *
     * @var array
     */
    protected static $weekendDays = array(
        self::SATURDAY,
        self::SUNDAY,
    );

    /**
     * A test Carbon instance to be returned when now instances are created.
     *
     * @var \Carbon\Carbon|null
     */
    protected static $testNow;

    /**
     * A translator to ... er ... translate stuff.
     *
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected static $translator;

    /**
     * The errors that can occur.
     *
     * @var array
     */
    protected static $lastErrors;

    /**
     * Will UTF8 encoding be used to print localized date/time ?
     *
     * @var bool
     */
    protected static $utf8 = false;

    /**
     * Indicates if months should be calculated with overflow.
     *
     * @var bool
     */
    protected static $monthsOverflow = true;

    /**
     * Indicates if months should be calculated with overflow.
     *
     * @param bool $monthsOverflow
     *
     * @return void
     */
    public static function useMonthsOverflow($monthsOverflow = true): void
    {
        static::$monthsOverflow = $monthsOverflow;
    }

    /**
     * Reset the month overflow behavior.
     *
     * @return void
     */
    public static function resetMonthsOverflow(): void
    {
        static::$monthsOverflow = true;
    }

    /**
     * Get the month overflow behavior.
     */
    public static function shouldOverflowMonths(): bool
    {
        return static::$monthsOverflow;
    }

    /**
     * Creates a DateTimeZone from a string, DateTimeZone or integer offset.
     *
     * @param \DateTimeZone|string|int|null $object
     *
     * @throws \InvalidArgumentException
     */
    protected static function safeCreateDateTimeZone($object): DateTimeZone
    {
        if ($object === null) {
            // Don't return null... avoid Bug #52063 in PHP <5.3.6
            return new DateTimeZone(\date_default_timezone_get());
        }

        if ($object instanceof DateTimeZone) {
            return $object;
        }

        if (\is_numeric($object)) {
            $tzName = \timezone_name_from_abbr('', $object * 3600, 1);

            if ($tzName === false) {
                throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
            }

            $object = $tzName;
        }

        $tz = @\timezone_open((string) $object);

        if ($tz === false) {
            throw new InvalidArgumentException('Unknown or bad timezone ('.$object.')');
        }

        return $tz;
    }

    ///////////////////////////////////////////////////////////////////
    //////////////////////////// CONSTRUCTORS /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Create a new Carbon instance.
     *
     * Please see the testing aids section (specifically static::setTestNow())
     * for more on the possibility of this constructor returning a test instance.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public function __construct(?string $time = null, $tz = null)
    {
        $time = $time ?: 'now';
        // If the class has a test now set and we are trying to create a now()
        // instance then override as required
        if (static::$testNow instanceof self && ($time === 'now' || static::hasRelativeKeywords($time))) {
            $testInstance = clone static::$testNow;
            if (static::hasRelativeKeywords($time)) {
                $testInstance->modify($time);
            }

            //shift the time according to the given time zone
            if ($tz !== null && $tz !== static::$testNow->getTimezone()) {
                $testInstance->setTimezone($tz);
            } else {
                $tz = $testInstance->getTimezone();
            }

            $time = $testInstance->toDateTimeString();
        }

        parent::__construct($time, static::safeCreateDateTimeZone($tz));
    }

    /**
     * Create a Carbon instance from a DateTime one.
     */
    public static function instance(DateTime $dt): self
    {
        if ($dt instanceof static) {
            return clone $dt;
        }

        return new static($dt->format('Y-m-d H:i:s.u'), $dt->getTimezone());
    }

    /**
     * Create a carbon instance from a string.
     *
     * This is an alias for the constructor that allows better fluent syntax
     * as it allows you to do Carbon::parse('Monday next week')->fn() rather
     * than (new Carbon('Monday next week'))->fn().
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function parse(?string $time = null, $tz = null): self
    {
        return new static($time, $tz);
    }

    /**
     * Get a Carbon instance for the current date and time.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function now($tz = null): self
    {
        return new static(null, $tz);
    }

    /**
     * Create a Carbon instance for today.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function today($tz = null): self
    {
        return static::now($tz)->startOfDay();
    }

    /**
     * Create a Carbon instance for tomorrow.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function tomorrow($tz = null): self
    {
        return static::today($tz)->addDay();
    }

    /**
     * Create a Carbon instance for yesterday.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function yesterday($tz = null): self
    {
        return static::today($tz)->subDay();
    }

    /**
     * Create a Carbon instance for the greatest supported date.
     */
    public static function maxValue(): self
    {
        if (PHP_INT_SIZE === 4) {
            // 32 bit (and additionally Windows 64 bit)
            return static::createFromTimestamp(PHP_INT_MAX);
        }

        // 64 bit
        return static::create(9999, 12, 31, 23, 59, 59);
    }

    /**
     * Create a Carbon instance for the lowest supported date.
     */
    public static function minValue(): self
    {
        if (PHP_INT_SIZE === 4) {
            // 32 bit (and additionally Windows 64 bit)
            return static::createFromTimestamp(~PHP_INT_MAX);
        }

        // 64 bit
        return static::create(1, 1, 1, 0, 0, 0);
    }

    /**
     * Create a new Carbon instance from a specific date and time.
     *
     * If any of $year, $month or $day are set to null their now() values will
     * be used.
     *
     * If $hour is null it will be set to its now() value and the default
     * values for $minute and $second will be their now() values.
     *
     * If $hour is not null then the default values for $minute and $second
     * will be 0.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function create(?int $year = null, ?int $month = null, ?int $day = null, ?int $hour = null, ?int $minute = null, ?int $second = null, $tz = null): self
    {
        $now = static::$testNow ? static::$testNow->getTimestamp() : \time();

        $defaults = \array_combine(array(
            'year',
            'month',
            'day',
            'hour',
            'minute',
            'second',
        ), \explode('-', \date('Y-n-j-G-i-s', $now)));

        $year = $year === null ? $defaults['year'] : $year;
        $month = $month === null ? $defaults['month'] : $month;
        $day = $day === null ? $defaults['day'] : $day;

        if ($hour === null) {
            $hour = $defaults['hour'];
            $minute = $minute === null ? $defaults['minute'] : $minute;
            $second = $second === null ? $defaults['second'] : $second;
        } else {
            $minute = $minute === null ? 0 : $minute;
            $second = $second === null ? 0 : $second;
        }

        $fixYear = null;

        if ($year < 0) {
            $fixYear = $year;
            $year = 0;
        } elseif ($year > 9999) {
            $fixYear = $year - 9999;
            $year = 9999;
        }

        $instance = static::createFromFormat('Y-n-j G:i:s', \sprintf('%s-%s-%s %s:%02s:%02s', $year, $month, $day, $hour, $minute, $second), $tz);

        if ($fixYear !== null) {
            $instance->addYears($fixYear);
        }

        return $instance;
    }

    /**
     * Create a new safe Carbon instance from a specific date and time.
     *
     * If any of $year, $month or $day are set to null their now() values will
     * be used.
     *
     * If $hour is null it will be set to its now() value and the default
     * values for $minute and $second will be their now() values.
     *
     * If $hour is not null then the default values for $minute and $second
     * will be 0.
     *
     * If one of the set values is not valid, an \InvalidArgumentException
     * will be thrown.
     *
     * @param \DateTimeZone|string|null $tz
     *
     * @throws \Carbon\Exceptions\InvalidDateException
     */
    public static function createSafe(?int $year = null, ?int $month = null, ?int $day = null, ?int $hour = null, ?int $minute = null, ?int $second = null, $tz = null): self
    {
        $fields = array(
            'year' => array(0, 9999),
            'month' => array(0, 12),
            'day' => array(0, 31),
            'hour' => array(0, 24),
            'minute' => array(0, 59),
            'second' => array(0, 59),
        );

        foreach ($fields as $field => $range) {
            if ($$field !== null && (!\is_int($$field) || $$field < $range[0] || $$field > $range[1])) {
                throw new InvalidDateException($field, $$field);
            }
        }

        $instance = static::create($year, $month, 1, $hour, $minute, $second, $tz);

        if ($day !== null && $day > $instance->daysInMonth) {
            throw new InvalidDateException('day', $day);
        }

        return $instance->day($day);
    }

    /**
     * Create a Carbon instance from just a date. The time portion is set to now.
     *
     * @param \DateTimeZone|string|null $tz
     *
     * @return static
     */
    public static function createFromDate(?int $year = null, ?int $month = null, ?int $day = null, $tz = null): self
    {
        return static::create($year, $month, $day, null, null, null, $tz);
    }

    /**
     * Create a Carbon instance from just a time. The date portion is set to today.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function createFromTime(?int $hour = null, ?int $minute = null, ?int $second = null, $tz = null): self
    {
        return static::create(null, null, null, $hour, $minute, $second, $tz);
    }

    /**
     * Create a Carbon instance from a specific format.
     *
     * @param string                    $format
     * @param string                    $time
     * @param \DateTimeZone|string|null $tz
     *
     * @throws \InvalidArgumentException
     */
    public static function createFromFormat($format, $time, $tz = null): self
    {
        if ($tz !== null) {
            $dt = parent::createFromFormat($format, $time, static::safeCreateDateTimeZone($tz));
        } else {
            $dt = parent::createFromFormat($format, $time);
        }

        static::setLastErrors($lastErrors = parent::getLastErrors());

        if ($dt instanceof DateTime) {
            return static::instance($dt);
        }

        throw new InvalidArgumentException(\implode(PHP_EOL, $lastErrors['errors']));
    }

    /**
     * Set last errors.
     */
    private static function setLastErrors(array $lastErrors): void
    {
        static::$lastErrors = $lastErrors;
    }

    /**
     * {@inheritdoc}
     */
    public static function getLastErrors()
    {
        return static::$lastErrors;
    }

    /**
     * Create a Carbon instance from a timestamp.
     *
     * @param \DateTimeZone|string|null $tz
     */
    public static function createFromTimestamp(int $timestamp, $tz = null): self
    {
        return static::now($tz)->setTimestamp($timestamp);
    }

    /**
     * Create a Carbon instance from an UTC timestamp.
     */
    public static function createFromTimestampUTC(int $timestamp): self
    {
        return new static('@'.$timestamp);
    }

    /**
     * Get a copy of the instance.
     */
    public function copy(): self
    {
        return clone $this;
    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////// GETTERS AND SETTERS /////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Get a part of the Carbon object
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return string|int|bool|\DateTimeZone
     */
    public function __get($name)
    {
        switch (true) {
            case \array_key_exists($name, $formats = array(
                'year' => 'Y',
                'yearIso' => 'o',
                'month' => 'n',
                'day' => 'j',
                'hour' => 'G',
                'minute' => 'i',
                'second' => 's',
                'micro' => 'u',
                'dayOfWeek' => 'w',
                'dayOfYear' => 'z',
                'weekOfYear' => 'W',
                'daysInMonth' => 't',
                'timestamp' => 'U',
            )):
                return (int) $this->format($formats[$name]);

            case $name === 'weekOfMonth':
                return (int) \ceil($this->day / static::DAYS_PER_WEEK);

            case $name === 'age':
                return $this->diffInYears();

            case $name === 'quarter':
                return (int) \ceil($this->month / static::MONTHS_PER_QUARTER);

            case $name === 'offset':
                return $this->getOffset();

            case $name === 'offsetHours':
                return $this->getOffset() / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR;

            case $name === 'dst':
                return $this->format('I') === '1';

            case $name === 'local':
                return $this->getOffset() === $this->copy()->setTimezone(\date_default_timezone_get())->getOffset();

            case $name === 'utc':
                return $this->getOffset() === 0;

            case $name === 'timezone' || $name === 'tz':
                return $this->getTimezone();

            case $name === 'timezoneName' || $name === 'tzName':
                return $this->getTimezone()->getName();

            default:
                throw new InvalidArgumentException(\sprintf("Unknown getter '%s'", $name));
        }
    }

    /**
     * Check if an attribute exists on the object
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        try {
            $this->__get($name);
        } catch (InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * Set a part of the Carbon object
     *
     * @param string                   $name
     * @param string|int|\DateTimeZone $value
     *
     * @throws \InvalidArgumentException
     */
    public function __set($name, $value): void
    {
        switch ($name) {
            case 'year':
            case 'month':
            case 'day':
            case 'hour':
            case 'minute':
            case 'second':
                list($year, $month, $day, $hour, $minute, $second) = \explode('-', $this->format('Y-n-j-G-i-s'));
                $$name = $value;
                $this->setDateTime((int) $year, (int) $month, (int) $day, (int) $hour, (int) $minute, (int) $second);
                break;

            case 'timestamp':
                parent::setTimestamp($value);
                break;

            case 'timezone':
            case 'tz':
                $this->setTimezone($value);
                break;

            default:
                throw new InvalidArgumentException(\sprintf("Unknown setter '%s'", $name));
        }
    }

    /**
     * Set the instance's year
     */
    public function year(int $value): self
    {
        $this->year = $value;

        return $this;
    }

    /**
     * Set the instance's month
     */
    public function month(int $value): self
    {
        $this->month = $value;

        return $this;
    }

    /**
     * Set the instance's day
     */
    public function day(int $value): self
    {
        $this->day = $value;

        return $this;
    }

    /**
     * Set the instance's hour
     */
    public function hour(int $value): self
    {
        $this->hour = $value;

        return $this;
    }

    /**
     * Set the instance's minute
     */
    public function minute(int $value): self
    {
        $this->minute = $value;

        return $this;
    }

    /**
     * Set the instance's second
     */
    public function second(int $value): self
    {
        $this->second = $value;

        return $this;
    }

    /**
     * Sets the current date of the DateTime object to a different date.
     * Calls modify as a workaround for a php bug
     *
     * @see https://github.com/briannesbitt/Carbon/issues/539
     * @see https://bugs.php.net/bug.php?id=63863
     *
     * @param int $year
     * @param int $month
     * @param int $day
     */
    public function setDate($year, $month, $day): self
    {
        $this->modify('+0 day');

        return parent::setDate($year, $month, $day);
    }

    /**
     * Set the date and time all together
     */
    public function setDateTime(int $year, int $month, int $day, int $hour, int $minute, int $second = 0): self
    {
        return $this->setDate($year, $month, $day)->setTime($hour, $minute, $second);
    }

    /**
     * Set the time by time string
     */
    public function setTimeFromTimeString(string $time): self
    {
        $time = \explode(':', $time);

        $hour = (int) $time[0];
        $minute = isset($time[1]) ? (int) $time[1] : 0;
        $second = isset($time[2]) ? (int) $time[2] : 0;

        return $this->setTime($hour, $minute, $second);
    }

    /**
     * Set the instance's timestamp
     */
    public function timestamp(int $value): self
    {
        return $this->setTimestamp($value);
    }

    /**
     * Alias for setTimezone()
     *
     * @param \DateTimeZone|string $value
     */
    public function timezone($value): self
    {
        return $this->setTimezone($value);
    }

    /**
     * Alias for setTimezone()
     *
     * @param \DateTimeZone|string $value
     */
    public function tz($value): self
    {
        return $this->setTimezone($value);
    }

    /**
     * Set the instance's timezone from a string or object
     *
     * @param \DateTimeZone|string $value
     */
    public function setTimezone($value): self
    {
        return parent::setTimezone(static::safeCreateDateTimeZone($value));
    }

    /**
     * Get the days of the week
     */
    public static function getDays(): array
    {
        return static::$days;
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////// WEEK SPECIAL DAYS /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Get the first day of week
     */
    public static function getWeekStartsAt(): int
    {
        return static::$weekStartsAt;
    }

    /**
     * Set the first day of week
     */
    public static function setWeekStartsAt(int $day): void
    {
        static::$weekStartsAt = $day;
    }

    /**
     * Get the last day of week
     */
    public static function getWeekEndsAt(): int
    {
        return static::$weekEndsAt;
    }

    /**
     * Set the last day of week
     */
    public static function setWeekEndsAt(int $day): void
    {
        static::$weekEndsAt = $day;
    }

    /**
     * Get weekend days
     *
     * @return array
     */
    public static function getWeekendDays(): array
    {
        return static::$weekendDays;
    }

    /**
     * Set weekend days
     */
    public static function setWeekendDays(array $days): void
    {
        static::$weekendDays = $days;
    }

    ///////////////////////////////////////////////////////////////////
    ///////////////////////// TESTING AIDS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Set a Carbon instance (real or mock) to be returned when a "now"
     * instance is created.  The provided instance will be returned
     * specifically under the following conditions:
     *   - A call to the static now() method, ex. Carbon::now()
     *   - When a null (or blank string) is passed to the constructor or parse(), ex. new Carbon(null)
     *   - When the string "now" is passed to the constructor or parse(), ex. new Carbon('now')
     *   - When a string containing the desired time is passed to Carbon::parse().
     *
     * Note the timezone parameter was left out of the examples above and
     * has no affect as the mock value will be returned regardless of its value.
     *
     * To clear the test instance call this method using the default
     * parameter of null.
     *
     * @param \Carbon\Carbon|string|null $testNow
     */
    public static function setTestNow($testNow = null): void
    {
        static::$testNow = \is_string($testNow) ? static::parse($testNow) : $testNow;
    }

    /**
     * Get the Carbon instance (real or mock) to be returned when a "now"
     * instance is created.
     *
     * @return static|null the current instance used for testing
     */
    public static function getTestNow(): ?self
    {
        return static::$testNow;
    }

    /**
     * Determine if there is a valid test instance set. A valid test instance
     * is anything that is not null.
     *
     * @return bool true if there is a test instance, otherwise false
     */
    public static function hasTestNow(): bool
    {
        return static::getTestNow() !== null;
    }

    /**
     * Determine if there is a relative keyword in the time string, this is to
     * create dates relative to now for test instances. e.g.: next tuesday
     *
     * @return bool true if there is a keyword, otherwise false
     */
    public static function hasRelativeKeywords(string $time): bool
    {
        // skip common format with a '-' in it
        if (\preg_match('/\d{4}-\d{1,2}-\d{1,2}/', $time) !== 1) {
            foreach (static::$relativeKeywords as $keyword) {
                if (\stripos($time, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////// LOCALIZATION //////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Initialize the translator instance if necessary.
     */
    protected static function translator(): TranslatorInterface
    {
        if (static::$translator === null) {
            $translator = new Translator('en');
            $translator->addLoader('array', new ArrayLoader());
            static::$translator = $translator;
            static::setLocale('en');
        }

        return static::$translator;
    }

    /**
     * Get the translator instance in use
     */
    public static function getTranslator(): TranslatorInterface
    {
        return static::translator();
    }

    /**
     * Set the translator instance to use
     */
    public static function setTranslator(TranslatorInterface $translator): void
    {
        static::$translator = $translator;
    }

    /**
     * Get the current translator locale
     */
    public static function getLocale(): string
    {
        return static::translator()->getLocale();
    }

    /**
     * Set the current translator locale and indicate if the source locale file exists
     */
    public static function setLocale(string $locale): bool
    {
        $locale = \preg_replace_callback('/\b([a-z]{2})[-_](?:([a-z]{4})[-_])?([a-z]{2})\b/', function ($matches) {
            return $matches[1].'_'.(!empty($matches[2]) ? \ucfirst($matches[2]).'_' : '').\strtoupper($matches[3]);
        }, \strtolower($locale));

        if (\file_exists($filename = __DIR__.'/Lang/'.$locale.'.php')) {
            $translator = static::translator();
            $translator->setLocale($locale);

            if ($translator instanceof Translator) {
                // Ensure the locale has been loaded.
                $translator->addResource('array', require $filename, $locale);
            }

            return true;
        }

        return false;
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////// STRING FORMATTING /////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Set if UTF8 will be used for localized date/time
     *
     * @param bool $utf8
     */
    public static function setUtf8(bool $utf8): void
    {
        static::$utf8 = $utf8;
    }

    /**
     * Format the instance with the current locale.  You can set the current
     * locale using setlocale() http://php.net/setlocale.
     */
    public function formatLocalized(string $format): string
    {
        // Check for Windows to find and replace the %e
        // modifier correctly
        if (\strtoupper(\substr(PHP_OS, 0, 3)) === 'WIN') {
            $format = \preg_replace('#(?<!%)((?:%%)*)%e#', '\1%#d', $format);
        }

        $formatted = \strftime($format, \strtotime((string) $this));

        return static::$utf8 ? \utf8_encode($formatted) : $formatted;
    }

    /**
     * Reset the format used to the default when type juggling a Carbon instance to a string
     */
    public static function resetToStringFormat(): void
    {
        static::setToStringFormat(static::DEFAULT_TO_STRING_FORMAT);
    }

    /**
     * Set the default format used when type juggling a Carbon instance to a string
     */
    public static function setToStringFormat(string $format): void
    {
        static::$toStringFormat = $format;
    }

    /**
     * Format the instance as a string using the set format
     */
    public function __toString()
    {
        return $this->format(static::$toStringFormat);
    }

    /**
     * Format the instance as date
     */
    public function toDateString(): string
    {
        return $this->format('Y-m-d');
    }

    /**
     * Format the instance as a readable date
     */
    public function toFormattedDateString(): string
    {
        return $this->format('M j, Y');
    }

    /**
     * Format the instance as time
     */
    public function toTimeString(): string
    {
        return $this->format('H:i:s');
    }

    /**
     * Format the instance as date and time
     */
    public function toDateTimeString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }

    /**
     * Format the instance with day, date and time
     */
    public function toDayDateTimeString(): string
    {
        return $this->format('D, M j, Y g:i A');
    }

    /**
     * Format the instance as ATOM
     */
    public function toAtomString(): string
    {
        return $this->format(static::ATOM);
    }

    /**
     * Format the instance as COOKIE
     */
    public function toCookieString(): string
    {
        return $this->format(static::COOKIE);
    }

    /**
     * Format the instance as ISO8601
     */
    public function toIso8601String(): string
    {
        return $this->toAtomString();
    }

    /**
     * Format the instance as RFC822
     */
    public function toRfc822String(): string
    {
        return $this->format(static::RFC822);
    }

    /**
     * Format the instance as RFC850
     */
    public function toRfc850String(): string
    {
        return $this->format(static::RFC850);
    }

    /**
     * Format the instance as RFC1036
     */
    public function toRfc1036String(): string
    {
        return $this->format(static::RFC1036);
    }

    /**
     * Format the instance as RFC1123
     */
    public function toRfc1123String(): string
    {
        return $this->format(static::RFC1123);
    }

    /**
     * Format the instance as RFC2822
     */
    public function toRfc2822String(): string
    {
        return $this->format(static::RFC2822);
    }

    /**
     * Format the instance as RFC3339
     */
    public function toRfc3339String(): string
    {
        return $this->format(static::RFC3339);
    }

    /**
     * Format the instance as RSS
     */
    public function toRssString(): string
    {
        return $this->format(static::RSS);
    }

    /**
     * Format the instance as W3C
     */
    public function toW3cString(): string
    {
        return $this->format(static::W3C);
    }

    ///////////////////////////////////////////////////////////////////
    ////////////////////////// COMPARISONS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Determines if the instance is equal to another
     */
    public function eq(self $dt): bool
    {
        return $this->format('U') === $dt->format('U');
    }

    /**
     * Determines if the instance is equal to another
     *
     * @see eq()
     */
    public function equalTo(self $dt): bool
    {
        return $this->eq($dt);
    }

    /**
     * Determines if the instance is not equal to another
     */
    public function ne(self $dt): bool
    {
        return !$this->eq($dt);
    }

    /**
     * Determines if the instance is not equal to another
     *
     * @see ne()
     */
    public function notEqualTo(self $dt): bool
    {
        return $this->ne($dt);
    }

    /**
     * Determines if the instance is greater (after) than another
     */
    public function gt(self $dt): bool
    {
        return $this > $dt;
    }

    /**
     * Determines if the instance is greater (after) than another
     *
     * @see gt()
     */
    public function greaterThan(self $dt): bool
    {
        return $this->gt($dt);
    }

    /**
     * Determines if the instance is greater (after) than or equal to another
     */
    public function gte(self $dt): bool
    {
        return $this >= $dt;
    }

    /**
     * Determines if the instance is greater (after) than or equal to another
     *
     * @see gte()
     */
    public function greaterThanOrEqualTo(self $dt): bool
    {
        return $this->gte($dt);
    }

    /**
     * Determines if the instance is less (before) than another
     */
    public function lt(self $dt): bool
    {
        return $this < $dt;
    }

    /**
     * Determines if the instance is less (before) than another
     *
     * @see lt()
     */
    public function lessThan(self $dt): bool
    {
        return $this->lt($dt);
    }

    /**
     * Determines if the instance is less (before) or equal to another
     */
    public function lte(self $dt): bool
    {
        return $this <= $dt;
    }

    /**
     * Determines if the instance is less (before) or equal to another
     *
     * @see lte()
     */
    public function lessThanOrEqualTo(self $dt): bool
    {
        return $this->lte($dt);
    }

    /**
     * Determines if the instance is between two others
     *
     * @param bool $equal Indicates if a > and < comparison should be used or <= or >=
     */
    public function between(self $dt1, self $dt2, bool $equal = true): bool
    {
        if ($dt1->gt($dt2)) {
            $temp = $dt1;
            $dt1 = $dt2;
            $dt2 = $temp;
        }

        if ($equal) {
            return $this->gte($dt1) && $this->lte($dt2);
        }

        return $this->gt($dt1) && $this->lt($dt2);
    }

    /**
     * Get the closest date from the instance.
     */
    public function closest(self $dt1, self $dt2): self
    {
        return $this->diffInSeconds($dt1) < $this->diffInSeconds($dt2) ? $dt1 : $dt2;
    }

    /**
     * Get the farthest date from the instance.
     */
    public function farthest(self $dt1, self $dt2): self
    {
        return $this->diffInSeconds($dt1) > $this->diffInSeconds($dt2) ? $dt1 : $dt2;
    }

    /**
     * Get the minimum instance between a given instance (default now) and the current instance.
     */
    public function min(self $dt = null): self
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return $this->lt($dt) ? $this : $dt;
    }

    /**
     * Get the minimum instance between a given instance (default now) and the current instance.
     *
     * @see min()
     */
    public function minimum(self $dt = null): self
    {
        return $this->min($dt);
    }

    /**
     * Get the maximum instance between a given instance (default now) and the current instance.
     */
    public function max(self $dt = null): self
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return $this->gt($dt) ? $this : $dt;
    }

    /**
     * Get the maximum instance between a given instance (default now) and the current instance.
     *
     * @see max()
     */
    public function maximum(self $dt = null): self
    {
        return $this->max($dt);
    }

    /**
     * Determines if the instance is a weekday
     */
    public function isWeekday(): bool
    {
        return !$this->isWeekend();
    }

    /**
     * Determines if the instance is a weekend day
     */
    public function isWeekend(): bool
    {
        return \in_array($this->dayOfWeek, static::$weekendDays);
    }

    /**
     * Determines if the instance is yesterday
     */
    public function isYesterday(): bool
    {
        return $this->toDateString() === static::yesterday($this->getTimezone())->toDateString();
    }

    /**
     * Determines if the instance is today
     */
    public function isToday(): bool
    {
        return $this->toDateString() === static::now($this->getTimezone())->toDateString();
    }

    /**
     * Determines if the instance is tomorrow
     */
    public function isTomorrow(): bool
    {
        return $this->toDateString() === static::tomorrow($this->getTimezone())->toDateString();
    }

    /**
     * Determines if the instance is within the next week
     */
    public function isNextWeek(): bool
    {
        return $this->weekOfYear === static::now($this->getTimezone())->addWeek()->weekOfYear;
    }

    /**
     * Determines if the instance is within the last week
     */
    public function isLastWeek(): bool
    {
        return $this->weekOfYear === static::now($this->getTimezone())->subWeek()->weekOfYear;
    }

    /**
     * Determines if the instance is within the next month
     */
    public function isNextMonth(): bool
    {
        return $this->month === static::now($this->getTimezone())->addMonthNoOverflow()->month;
    }

    /**
     * Determines if the instance is within the last month
     */
    public function isLastMonth(): bool
    {
        return $this->month === static::now($this->getTimezone())->subMonthNoOverflow()->month;
    }

    /**
     * Determines if the instance is within next year
     */
    public function isNextYear(): bool
    {
        return $this->year === static::now($this->getTimezone())->addYear()->year;
    }

    /**
     * Determines if the instance is within the previous year
     */
    public function isLastYear(): bool
    {
        return $this->year === static::now($this->getTimezone())->subYear()->year;
    }

    /**
     * Determines if the instance is in the future, ie. greater (after) than now
     */
    public function isFuture(): bool
    {
        return $this->gt(static::now($this->getTimezone()));
    }

    /**
     * Determines if the instance is in the past, ie. less (before) than now
     */
    public function isPast(): bool
    {
        return $this->lt(static::now($this->getTimezone()));
    }

    /**
     * Determines if the instance is a leap year
     */
    public function isLeapYear(): bool
    {
        return $this->format('L') === '1';
    }

    /**
     * Determines if the instance is a long year
     *
     * @see https://en.wikipedia.org/wiki/ISO_8601#Week_dates
     */
    public function isLongYear(): bool
    {
        return static::create($this->year, 12, 28, 0, 0, 0, $this->tz)->weekOfYear === 53;
    }

    /**
     * Compares the formatted values of the two dates.
     *
     * @param string              $format The date formats to compare.
     * @param \Carbon\Carbon|null $dt     The instance to compare with or null to use current day.
     */
    public function isSameAs(string $format, ?self $dt = null): bool
    {
        $dt = $dt ?: static::now($this->tz);

        return $this->format($format) === $dt->format($format);
    }

    /**
     * Determines if the instance is in the current year
     */
    public function isCurrentYear(): bool
    {
        return $this->isSameYear();
    }

    /**
     * Checks if the passed in date is in the same year as the instance year.
     *
     * @param \Carbon\Carbon|null $dt The instance to compare with or null to use current day.
     */
    public function isSameYear(self $dt = null): bool
    {
        return $this->isSameAs('Y', $dt);
    }

    /**
     * Determines if the instance is in the current month
     */
    public function isCurrentMonth(): bool
    {
        return $this->isSameMonth();
    }

    /**
     * Checks if the passed in date is in the same month as the instance month (and year if needed).
     *
     * @param \Carbon\Carbon|null $dt         The instance to compare with or null to use current day.
     * @param bool                $ofSameYear Check if it is the same month in the same year.
     */
    public function isSameMonth(self $dt = null, $ofSameYear = false): bool
    {
        $format = $ofSameYear ? 'Y-m' : 'm';

        return $this->isSameAs($format, $dt);
    }

    /**
     * Checks if the passed in date is the same day as the instance current day.
     */
    public function isSameDay(self $dt): bool
    {
        return $this->toDateString() === $dt->toDateString();
    }

    /**
     * Checks if this day is a Sunday.
     */
    public function isSunday(): bool
    {
        return $this->dayOfWeek === static::SUNDAY;
    }

    /**
     * Checks if this day is a Monday.
     */
    public function isMonday(): bool
    {
        return $this->dayOfWeek === static::MONDAY;
    }

    /**
     * Checks if this day is a Tuesday.
     */
    public function isTuesday(): bool
    {
        return $this->dayOfWeek === static::TUESDAY;
    }

    /**
     * Checks if this day is a Wednesday.
     */
    public function isWednesday(): bool
    {
        return $this->dayOfWeek === static::WEDNESDAY;
    }

    /**
     * Checks if this day is a Thursday.
     */
    public function isThursday(): bool
    {
        return $this->dayOfWeek === static::THURSDAY;
    }

    /**
     * Checks if this day is a Friday.
     */
    public function isFriday(): bool
    {
        return $this->dayOfWeek === static::FRIDAY;
    }

    /**
     * Checks if this day is a Saturday.
     */
    public function isSaturday(): bool
    {
        return $this->dayOfWeek === static::SATURDAY;
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////// ADDITIONS AND SUBTRACTIONS ////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Add years to the instance. Positive $value travel forward while
     * negative $value travel into the past.
     */
    public function addYears(int $value): self
    {
        return $this->modify($value.' year');
    }

    /**
     * Add a year to the instance
     */
    public function addYear(int $value = 1): self
    {
        return $this->addYears($value);
    }

    /**
     * Remove a year from the instance
     */
    public function subYear(int $value = 1): self
    {
        return $this->subYears($value);
    }

    /**
     * Remove years from the instance.
     */
    public function subYears(int $value): self
    {
        return $this->addYears(-1 * $value);
    }

    /**
     * Add quarters to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addQuarters(int $value): self
    {
        return $this->addMonths(static::MONTHS_PER_QUARTER * $value);
    }

    /**
     * Add a quarter to the instance
     */
    public function addQuarter(int $value = 1): self
    {
        return $this->addQuarters($value);
    }

    /**
     * Remove a quarter from the instance
     */
    public function subQuarter(int $value = 1): self
    {
        return $this->subQuarters($value);
    }

    /**
     * Remove quarters from the instance
     */
    public function subQuarters(int $value): self
    {
        return $this->addQuarters(-1 * $value);
    }

    /**
     * Add centuries to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addCenturies(int $value): self
    {
        return $this->addYears(static::YEARS_PER_CENTURY * $value);
    }

    /**
     * Add a century to the instance
     */
    public function addCentury(int $value = 1): self
    {
        return $this->addCenturies($value);
    }

    /**
     * Remove a century from the instance
     */
    public function subCentury(int $value = 1): self
    {
        return $this->subCenturies($value);
    }

    /**
     * Remove centuries from the instance
     */
    public function subCenturies(int $value): self
    {
        return $this->addCenturies(-1 * $value);
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addMonths(int $value): self
    {
        if (static::shouldOverflowMonths()) {
            return $this->addMonthsWithOverflow($value);
        }

        return $this->addMonthsNoOverflow($value);
    }

    /**
     * Add a month to the instance
     */
    public function addMonth(int $value = 1): self
    {
        return $this->addMonths($value);
    }

    /**
     * Remove a month from the instance
     */
    public function subMonth(int $value = 1): self
    {
        return $this->subMonths($value);
    }

    /**
     * Remove months from the instance
     */
    public function subMonths(int $value): self
    {
        return $this->addMonths(-1 * $value);
    }

    /**
     * Add months to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addMonthsWithOverflow(int $value): self
    {
        return $this->modify($value.' month');
    }

    /**
     * Add a month to the instance
     */
    public function addMonthWithOverflow(int $value = 1): self
    {
        return $this->addMonthsWithOverflow($value);
    }

    /**
     * Remove a month from the instance
     */
    public function subMonthWithOverflow(int $value = 1): self
    {
        return $this->subMonthsWithOverflow($value);
    }

    /**
     * Remove months from the instance
     */
    public function subMonthsWithOverflow(int $value): self
    {
        return $this->addMonthsWithOverflow(-1 * $value);
    }

    /**
     * Add months without overflowing to the instance. Positive $value
     * travels forward while negative $value travels into the past.
     */
    public function addMonthsNoOverflow(int $value): self
    {
        $day = $this->day;

        $this->modify($value.' month');

        if ($day !== $this->day) {
            $this->modify('last day of previous month');
        }

        return $this;
    }

    /**
     * Add a month with no overflow to the instance
     */
    public function addMonthNoOverflow(int $value = 1): self
    {
        return $this->addMonthsNoOverflow($value);
    }

    /**
     * Remove a month with no overflow from the instance
     */
    public function subMonthNoOverflow(int $value = 1): self
    {
        return $this->subMonthsNoOverflow($value);
    }

    /**
     * Remove months with no overflow from the instance
     */
    public function subMonthsNoOverflow(int $value): self
    {
        return $this->addMonthsNoOverflow(-1 * $value);
    }

    /**
     * Add days to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addDays(int $value): self
    {
        return $this->modify($value.' day');
    }

    /**
     * Add a day to the instance
     */
    public function addDay(int $value = 1): self
    {
        return $this->addDays($value);
    }

    /**
     * Remove a day from the instance
     */
    public function subDay(int $value = 1): self
    {
        return $this->subDays($value);
    }

    /**
     * Remove days from the instance
     */
    public function subDays(int $value): self
    {
        return $this->addDays(-1 * $value);
    }

    /**
     * Add weekdays to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addWeekdays(int $value): self
    {
        // fix for https://bugs.php.net/bug.php?id=54909
        $t = $this->toTimeString();
        $this->modify($value.' weekday');

        return $this->setTimeFromTimeString($t);
    }

    /**
     * Add a weekday to the instance
     */
    public function addWeekday(int $value = 1): self
    {
        return $this->addWeekdays($value);
    }

    /**
     * Remove a weekday from the instance
     */
    public function subWeekday(int $value = 1): self
    {
        return $this->subWeekdays($value);
    }

    /**
     * Remove weekdays from the instance
     */
    public function subWeekdays(int $value): self
    {
        return $this->addWeekdays(-1 * $value);
    }

    /**
     * Add weeks to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addWeeks(int $value): self
    {
        return $this->modify($value.' week');
    }

    /**
     * Add a week to the instance
     */
    public function addWeek(int $value = 1): self
    {
        return $this->addWeeks($value);
    }

    /**
     * Remove a week from the instance
     */
    public function subWeek(int $value = 1): self
    {
        return $this->subWeeks($value);
    }

    /**
     * Remove weeks to the instance
     */
    public function subWeeks(int $value): self
    {
        return $this->addWeeks(-1 * $value);
    }

    /**
     * Add hours to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addHours(int $value): self
    {
        return $this->modify($value.' hour');
    }

    /**
     * Add an hour to the instance
     */
    public function addHour(int $value = 1): self
    {
        return $this->addHours($value);
    }

    /**
     * Remove an hour from the instance
     */
    public function subHour(int $value = 1): self
    {
        return $this->subHours($value);
    }

    /**
     * Remove hours from the instance
     */
    public function subHours(int $value): self
    {
        return $this->addHours(-1 * $value);
    }

    /**
     * Add minutes to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addMinutes(int $value): self
    {
        return $this->modify($value.' minute');
    }

    /**
     * Add a minute to the instance
     */
    public function addMinute(int $value = 1): self
    {
        return $this->addMinutes($value);
    }

    /**
     * Remove a minute from the instance
     */
    public function subMinute(int $value = 1): self
    {
        return $this->subMinutes($value);
    }

    /**
     * Remove minutes from the instance
     */
    public function subMinutes(int $value): self
    {
        return $this->addMinutes(-1 * $value);
    }

    /**
     * Add seconds to the instance. Positive $value travels forward while
     * negative $value travels into the past.
     */
    public function addSeconds(int $value): self
    {
        return $this->modify($value.' second');
    }

    /**
     * Add a second to the instance
     */
    public function addSecond(int $value = 1): self
    {
        return $this->addSeconds($value);
    }

    /**
     * Remove a second from the instance
     */
    public function subSecond(int $value = 1): self
    {
        return $this->subSeconds($value);
    }

    /**
     * Remove seconds from the instance
     */
    public function subSeconds(int $value): self
    {
        return $this->addSeconds(-1 * $value);
    }

    ///////////////////////////////////////////////////////////////////
    /////////////////////////// DIFFERENCES ///////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Get the difference in years
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInYears(?self $dt = null, bool $abs = true)
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return (int) $this->diff($dt, $abs)->format('%r%y');
    }

    /**
     * Get the difference in months
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInMonths(?self $dt = null, bool $abs = true)
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return $this->diffInYears($dt, $abs) * static::MONTHS_PER_YEAR + (int) $this->diff($dt, $abs)->format('%r%m');
    }

    /**
     * Get the difference in weeks
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInWeeks(?self $dt = null, bool $abs = true)
    {
        return (int) ($this->diffInDays($dt, $abs) / static::DAYS_PER_WEEK);
    }

    /**
     * Get the difference in days
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInDays(?self $dt = null, bool $abs = true)
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return (int) $this->diff($dt, $abs)->format('%r%a');
    }

    /**
     * Get the difference in days using a filter closure
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInDaysFiltered(Closure $callback, ?self $dt = null, bool $abs = true)
    {
        return $this->diffFiltered(CarbonInterval::day(), $callback, $dt, $abs);
    }

    /**
     * Get the difference in hours using a filter closure
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInHoursFiltered(Closure $callback, ?self $dt = null, bool $abs = true)
    {
        return $this->diffFiltered(CarbonInterval::hour(), $callback, $dt, $abs);
    }

    /**
     * Get the difference by the given interval using a filter closure
     *
     * @param CarbonInterval $ci  An interval to traverse by
     * @param bool           $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffFiltered(CarbonInterval $ci, Closure $callback, ?self $dt = null, bool $abs = true)
    {
        $start = $this;
        $end = $dt ?: static::now($this->getTimezone());
        $inverse = false;

        if ($end < $start) {
            $start = $end;
            $end = $this;
            $inverse = true;
        }

        $period = new DatePeriod($start, $ci, $end);
        $vals = \array_filter(\iterator_to_array($period), function (DateTime $date) use ($callback) {
            return \call_user_func($callback, Carbon::instance($date));
        });

        $diff = \count($vals);

        return $inverse && !$abs ? -$diff : $diff;
    }

    /**
     * Get the difference in weekdays
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInWeekdays(?self $dt = null, bool $abs = true)
    {
        return $this->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekday();
        }, $dt, $abs);
    }

    /**
     * Get the difference in weekend days using a filter
     *
     * @param bool $abs Get the absolute of the difference
     *
     * @return int|float
     */
    public function diffInWeekendDays(?self $dt = null, bool $abs = true)
    {
        return $this->diffInDaysFiltered(function (Carbon $date) {
            return $date->isWeekend();
        }, $dt, $abs);
    }

    /**
     * Get the difference in hours
     *
     * @param bool $abs Get the absolute of the difference
     */
    public function diffInHours(?self $dt = null, bool $abs = true): int
    {
        return (int) ($this->diffInSeconds($dt, $abs) / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR);
    }

    /**
     * Get the difference in minutes
     *
     * @param bool $abs Get the absolute of the difference
     */
    public function diffInMinutes(?self $dt = null, bool $abs = true): int
    {
        return (int) ($this->diffInSeconds($dt, $abs) / static::SECONDS_PER_MINUTE);
    }

    /**
     * Get the difference in seconds
     *
     * @param bool $abs Get the absolute of the difference
     */
    public function diffInSeconds(?self $dt = null, $abs = true): int
    {
        $dt = $dt ?: static::now($this->getTimezone());
        $diff = $this->diff($dt);
        $value = $diff->days * 24 * 3600 + $diff->h * 3600 + $diff->i * 60 + $diff->s;

        return $abs || !$diff->invert ? $value : -$value;
    }

    /**
     * The number of seconds since midnight.
     */
    public function secondsSinceMidnight(): int
    {
        return $this->diffInSeconds($this->copy()->startOfDay());
    }

    /**
     * The number of seconds until 23:59:59.
     */
    public function secondsUntilEndOfDay(): int
    {
        return $this->diffInSeconds($this->copy()->endOfDay());
    }

    /**
     * Get the difference in a human readable format in the current locale.
     *
     * When comparing a value in the past to default now:
     * 1 hour ago
     * 5 months ago
     *
     * When comparing a value in the future to default now:
     * 1 hour from now
     * 5 months from now
     *
     * When comparing a value in the past to another value:
     * 1 hour before
     * 5 months before
     *
     * When comparing a value in the future to another value:
     * 1 hour after
     * 5 months after
     *
     * @param bool $absolute removes time difference modifiers ago, after, etc
     * @param bool $short    displays short format of time units
     *
     * @return string
     */
    public function diffForHumans(?self $other = null, bool $absolute = false, bool $short = false): string
    {
        $isNow = $other === null;

        if ($isNow) {
            $other = static::now($this->getTimezone());
        }

        $diffInterval = $this->diff($other);

        switch (true) {
            case $diffInterval->y > 0:
                $unit = $short ? 'y' : 'year';
                $count = $diffInterval->y;
                break;

            case $diffInterval->m > 0:
                $unit = $short ? 'm' : 'month';
                $count = $diffInterval->m;
                break;

            case $diffInterval->d > 0:
                $unit = $short ? 'd' : 'day';
                $count = $diffInterval->d;

                if ($count >= static::DAYS_PER_WEEK) {
                    $unit = $short ? 'w' : 'week';
                    $count = (int) ($count / static::DAYS_PER_WEEK);
                }
                break;

            case $diffInterval->h > 0:
                $unit = $short ? 'h' : 'hour';
                $count = $diffInterval->h;
                break;

            case $diffInterval->i > 0:
                $unit = $short ? 'min' : 'minute';
                $count = $diffInterval->i;
                break;

            default:
                $count = $diffInterval->s;
                $unit = $short ? 's' : 'second';
                break;
        }

        if ($count === 0) {
            $count = 1;
        }

        $time = static::translator()->transChoice($unit, $count, array(':count' => $count));

        if ($absolute) {
            return $time;
        }

        $isFuture = $diffInterval->invert === 1;

        $transId = $isNow ? ($isFuture ? 'from_now' : 'ago') : ($isFuture ? 'after' : 'before');

        // Some langs have special pluralization for past and future tense.
        $tryKeyExists = $unit.'_'.$transId;
        if ($tryKeyExists !== static::translator()->transChoice($tryKeyExists, $count)) {
            $time = static::translator()->transChoice($tryKeyExists, $count, array(':count' => $count));
        }

        return static::translator()->trans($transId, array(':time' => $time));
    }

    ///////////////////////////////////////////////////////////////////
    //////////////////////////// MODIFIERS ////////////////////////////
    ///////////////////////////////////////////////////////////////////

    /**
     * Resets the time to 00:00:00
     */
    public function startOfDay(): self
    {
        return $this->setTime(0, 0, 0);
    }

    /**
     * Resets the time to 23:59:59
     */
    public function endOfDay(): self
    {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Resets the date to the first day of the month and the time to 00:00:00
     */
    public function startOfMonth(): self
    {
        return $this->setDateTime($this->year, $this->month, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the month and time to 23:59:59
     */
    public function endOfMonth(): self
    {
        return $this->setDateTime($this->year, $this->month, $this->daysInMonth, 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the quarter and the time to 00:00:00
     */
    public function startOfQuarter(): self
    {
        $month = ($this->quarter - 1) * static::MONTHS_PER_QUARTER + 1;

        return $this->setDateTime($this->year, $month, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the quarter and time to 23:59:59
     */
    public function endOfQuarter(): self
    {
        return $this->startOfQuarter()->addMonths(static::MONTHS_PER_QUARTER - 1)->endOfMonth();
    }

    /**
     * Resets the date to the first day of the year and the time to 00:00:00
     */
    public function startOfYear(): self
    {
        return $this->setDateTime($this->year, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the year and time to 23:59:59
     */
    public function endOfYear(): self
    {
        return $this->setDateTime($this->year, 12, 31, 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the decade and the time to 00:00:00
     */
    public function startOfDecade(): self
    {
        $year = $this->year - $this->year % static::YEARS_PER_DECADE;

        return $this->setDateTime($year, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the decade and time to 23:59:59
     */
    public function endOfDecade(): self
    {
        $year = $this->year - $this->year % static::YEARS_PER_DECADE + static::YEARS_PER_DECADE - 1;

        return $this->setDateTime($year, 12, 31, 23, 59, 59);
    }

    /**
     * Resets the date to the first day of the century and the time to 00:00:00
     */
    public function startOfCentury(): self
    {
        $year = $this->year - ($this->year - 1) % static::YEARS_PER_CENTURY;

        return $this->setDateTime($year, 1, 1, 0, 0, 0);
    }

    /**
     * Resets the date to end of the century and time to 23:59:59
     */
    public function endOfCentury(): self
    {
        $year = $this->year - 1 - ($this->year - 1) % static::YEARS_PER_CENTURY + static::YEARS_PER_CENTURY;

        return $this->setDateTime($year, 12, 31, 23, 59, 59);
    }

    /**
     * Resets the date to the first day of week (defined in $weekStartsAt) and the time to 00:00:00
     */
    public function startOfWeek(): self
    {
        while ($this->dayOfWeek !== static::$weekStartsAt) {
            $this->subDay();
        }

        return $this->startOfDay();
    }

    /**
     * Resets the date to end of week (defined in $weekEndsAt) and time to 23:59:59
     */
    public function endOfWeek(): self
    {
        while ($this->dayOfWeek !== static::$weekEndsAt) {
            $this->addDay();
        }

        return $this->endOfDay();
    }

    /**
     * Modify to the next occurrence of a given day of the week.
     * If no dayOfWeek is provided, modify to the next occurrence
     * of the current day of the week.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function next(int $dayOfWeek = null): self
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }

        return $this->startOfDay()->modify('next '.static::$days[$dayOfWeek]);
    }

    /**
     * Go forward or backward to the next week- or weekend-day.
     */
    private function nextOrPreviousDay(bool $weekday = true, bool $forward = true): self
    {
        $step = $forward ? 1 : -1;

        do {
            $this->addDay($step);
        } while ($weekday ? $this->isWeekend() : $this->isWeekday());

        return $this;
    }

    /**
     * Go forward to the next weekday.
     */
    public function nextWeekday(): self
    {
        return $this->nextOrPreviousDay();
    }

    /**
     * Go backward to the previous weekday.
     */
    public function previousWeekday(): self
    {
        return $this->nextOrPreviousDay(true, false);
    }

    /**
     * Go forward to the next weekend day.
     */
    public function nextWeekendDay(): self
    {
        return $this->nextOrPreviousDay(false);
    }

    /**
     * Go backward to the previous weekend day.
     */
    public function previousWeekendDay(): self
    {
        return $this->nextOrPreviousDay(false, false);
    }

    /**
     * Modify to the previous occurrence of a given day of the week.
     * If no dayOfWeek is provided, modify to the previous occurrence
     * of the current day of the week.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function previous(int $dayOfWeek = null): self
    {
        if ($dayOfWeek === null) {
            $dayOfWeek = $this->dayOfWeek;
        }

        return $this->startOfDay()->modify('last '.static::$days[$dayOfWeek]);
    }

    /**
     * Modify to the first occurrence of a given day of the week
     * in the current month. If no dayOfWeek is provided, modify to the
     * first day of the current month.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function firstOfMonth(int $dayOfWeek = null): self
    {
        $this->startOfDay();

        if ($dayOfWeek === null) {
            return $this->day(1);
        }

        return $this->modify('first '.static::$days[$dayOfWeek].' of '.$this->format('F').' '.$this->year);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current month. If no dayOfWeek is provided, modify to the
     * last day of the current month.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function lastOfMonth(int $dayOfWeek = null): self
    {
        $this->startOfDay();

        if ($dayOfWeek === null) {
            return $this->day($this->daysInMonth);
        }

        return $this->modify('last '.static::$days[$dayOfWeek].' of '.$this->format('F').' '.$this->year);
    }

    /**
     * Modify to the given occurrence of a given day of the week
     * in the current month. If the calculated occurrence is outside the scope
     * of the current month, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @return mixed
     */
    public function nthOfMonth(int $nth, int $dayOfWeek)
    {
        $dt = $this->copy()->firstOfMonth();
        $check = $dt->format('Y-m');
        $dt->modify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return $dt->format('Y-m') === $check ? $this->modify((string) $dt) : false;
    }

    /**
     * Modify to the first occurrence of a given day of the week
     * in the current quarter. If no dayOfWeek is provided, modify to the
     * first day of the current quarter.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function firstOfQuarter(int $dayOfWeek = null): self
    {
        return $this->setDate($this->year, $this->quarter * static::MONTHS_PER_QUARTER - 2, 1)->firstOfMonth($dayOfWeek);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current quarter. If no dayOfWeek is provided, modify to the
     * last day of the current quarter.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function lastOfQuarter(int $dayOfWeek = null): self
    {
        return $this->setDate($this->year, $this->quarter * static::MONTHS_PER_QUARTER, 1)->lastOfMonth($dayOfWeek);
    }

    /**
     * Modify to the given occurrence of a given day of the week
     * in the current quarter. If the calculated occurrence is outside the scope
     * of the current quarter, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @return mixed
     */
    public function nthOfQuarter(int $nth, int $dayOfWeek)
    {
        $dt = $this->copy()->day(1)->month($this->quarter * static::MONTHS_PER_QUARTER);
        $lastMonth = $dt->month;
        $year = $dt->year;
        $dt->firstOfQuarter()->modify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return ($lastMonth < $dt->month || $year !== $dt->year) ? false : $this->modify((string) $dt);
    }

    /**
     * Modify to the first occurrence of a given day of the week
     * in the current year. If no dayOfWeek is provided, modify to the
     * first day of the current year.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function firstOfYear(int $dayOfWeek = null): self
    {
        return $this->month(1)->firstOfMonth($dayOfWeek);
    }

    /**
     * Modify to the last occurrence of a given day of the week
     * in the current year. If no dayOfWeek is provided, modify to the
     * last day of the current year.  Use the supplied constants
     * to indicate the desired dayOfWeek, ex. static::MONDAY.
     */
    public function lastOfYear(int $dayOfWeek = null): self
    {
        return $this->month(static::MONTHS_PER_YEAR)->lastOfMonth($dayOfWeek);
    }

    /**
     * Modify to the given occurrence of a given day of the week
     * in the current year. If the calculated occurrence is outside the scope
     * of the current year, then return false and no modifications are made.
     * Use the supplied constants to indicate the desired dayOfWeek, ex. static::MONDAY.
     *
     * @return mixed
     */
    public function nthOfYear(int $nth, int $dayOfWeek)
    {
        $dt = $this->copy()->firstOfYear()->modify('+'.$nth.' '.static::$days[$dayOfWeek]);

        return $this->year === $dt->year ? $this->modify((string) $dt) : false;
    }

    /**
     * Modify the current instance to the average of a given instance (default now) and the current instance.
     */
    public function average(?self $dt = null): self
    {
        $dt = $dt ?: static::now($this->getTimezone());

        return $this->addSeconds((int) ($this->diffInSeconds($dt, false) / 2));
    }

    /**
     * Check if its the birthday. Compares the date/month values of the two dates.
     *
     * @param \Carbon\Carbon|null $dt The instance to compare with or null to use current day.
     */
    public function isBirthday(?self $dt = null): bool
    {
        return $this->isSameAs('md', $dt);
    }

    /**
     * Return a serialized string of the instance.
     */
    public function serialize(): string
    {
        return \serialize($this);
    }

    /**
     * Create an instance form a serialized string.
     *
     * @throws \InvalidArgumentException
     */
    public static function fromSerialized(string $value): self
    {
        $instance = @\unserialize($value);

        if (!$instance instanceof static) {
            throw new InvalidArgumentException('Invalid serialized value.');
        }

        return $instance;
    }
}
