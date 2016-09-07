<?php declare(strict_types=1);

/*
 * This file is part of the datetime package.
 *
 * (c) Yakamara Media GmbH & Co. KG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yakamara;

use Yakamara\Holidays;
use Yakamara\Holidays\HolidaysInterface;

abstract class AbstractDateTime extends \DateTimeImmutable
{
    private static $defaultHolidays;

    /**
     * @param \DateTimeInterface $dateTime
     *
     * @return static
     */
    public static function createFromDateTime(\DateTimeInterface $dateTime): self
    {
        return new static($dateTime->format('Y-m-d H:i:s'));
    }

    /**
     * @param int $timestamp
     *
     * @return static
     */
    public static function createFromTimestamp(int $timestamp): self
    {
        return new static('@' . $timestamp);
    }

    public function formatLocalized(string $format): string
    {
        return strftime($format, $this->getTimestamp());
    }

    public function isWorkday(HolidaysInterface $holidays = null): bool
    {
        $holidays = $holidays ?: self::getDefaultHolidays();

        return $holidays->isWorkday($this);
    }

    public function isHoliday(HolidaysInterface $holidays = null): bool
    {
        $holidays = $holidays ?: self::getDefaultHolidays();

        return $holidays->isHoliday($this);
    }

    public function addWorkdays(int $days, HolidaysInterface $holidays = null): self
    {
        $holidays = $holidays ?: self::getDefaultHolidays();

        $interval = \DateInterval::createFromDateString($days . ' days');
        $days = abs($days);
        $date = $this;

        for ($i = 0; $i < $days; ++$i) {
            do {
                $date = $date->add($interval);
            } while ($holidays->isHoliday($date));
        }

        return $date;
    }

    public function diffWorkdays(\DateTimeInterface $date, HolidaysInterface $holidays = null)
    {
        $holidays = $holidays ?: self::getDefaultHolidays();

        $date1 = Date::createFromDateTime($this);
        $date2 = Date::createFromDateTime($date);

        if ($date1 < $date2) {
            $date = $date2;
            $date2 = $date1;
            $date1 = $date;
        }

        for ($i = 0; $date2 < $date1; ++$i) {
            $date2->addWorkdays(1, $holidays);
        }

        return $i;
    }

    public static function setDefaultHolidays(HolidaysInterface $holidays)
    {
        self::$defaultHolidays = $holidays;
    }

    public static function getDefaultHolidays(): HolidaysInterface
    {
        if (!self::$defaultHolidays) {
            self::$defaultHolidays = new Holidays\Germany();
        }

        return self::$defaultHolidays;
    }
}