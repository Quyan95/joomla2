<?php
/**
 * @package         Regular Labs Library
 * @version         23.10.25560
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Factory as JFactory;

class Date
{
    /**
     * Applies offset to a date
     *
     * @param string $date
     * @param string $timezone
     */
    public static function applyTimezone(&$date, $timezone = '')
    {
        if ($date <= 0)
        {
            $date = 0;

            return;
        }

        $user     = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $timezone = $timezone ?: $user->getParam('timezone', JFactory::getApplication()->get('offset'));

        $date = JFactory::getDate($date, $timezone);
        $date->setTimezone(new DateTimeZone('UTC'));

        $date = $date->format('Y-m-d H:i:s', true, false);
    }

    /**
     * Convert string with 'date' format to 'strftime' format
     *
     * @param string $format
     *
     * @return string
     */
    public static function dateToStrftimeFormat($format)
    {
        return strtr((string) $format, self::getDateToStrftimeFormats());
    }

    /**
     * Convert string to a correct date format ('00-00-00 00:00:00' or '00-00-00') or empty string
     *
     * @param string $date
     *
     * @return string
     */
    public static function fix($date)
    {
        if ( ! $date)
        {
            return '';
        }

        $date = trim($date);

        // Check if date has correct syntax: 00-00-00 00:00:00
        // If so, the date format is correct
        if (RegEx::match('^[0-9]+-[0-9]+-[0-9]+( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$', $date))
        {
            return $date;
        }

        // Check if date has syntax: 00-00-00 00:00
        // If so, it is missing the seconds, so add :00 (seconds)
        if (RegEx::match('^[0-9]+-[0-9]+-[0-9]+ [0-9][0-9]:[0-9][0-9]$', $date))
        {
            return $date . ':00';
        }

        // Check if date has a prepending date syntax: 00-00-00
        // If so, it is missing a correct time time, so add 00:00:00 (hours, minutes, seconds)
        if (RegEx::match('^([0-9]+-[0-9]+-[0-9]+)$', $date, $match))
        {
            return $match[1] . ' 00:00:00';
        }

        // Date format is not correct, so return empty string
        return '';
    }

    /**
     * Convert string to a correct time format: 1:23 to 01:23
     *
     * @param string $time
     *
     * @return string
     */
    public static function fixTime($time, $include_seconds = true)
    {
        [$hours, $minutes, $seconds] = explode(':', $time . '::');

        $hours   = str_pad($hours, 2, '0', STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

        if ( ! $include_seconds)
        {
            return $hours . ':' . $minutes;
        }

        return $hours . ':' . $minutes . ':' . $seconds;
    }

    /**
     * Convert string with 'date' format to 'strftime' format
     *
     * @param string $format
     *
     * @return string
     */
    public static function strftimeToDateFormat($format)
    {
        if ( ! str_contains($format, '%'))
        {
            return $format;
        }

        return strtr((string) $format, self::getStrftimeToDateFormats());
    }

    private static function getDateToStrftimeFormats()
    {
        return [
            // Day - no strf eq : S
            'd'  => '%d',
            'D'  => '%a',
            'jS' => '%#d[TH]',
            'j'  => '%#d',
            'l'  => '%A',
            'N'  => '%u',
            'w'  => '%w',
            'z'  => '%j',
            // Week - no date eq : %U, %W
            'W'  => '%V',
            // Month - no strf eq : n, t
            'F'  => '%B',
            'm'  => '%m',
            'M'  => '%b',
            // Year - no strf eq : L; no date eq : %C, %g
            'o'  => '%G',
            'Y'  => '%Y',
            'y'  => '%y',
            // Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
            'a'  => '%P',
            'A'  => '%p',
            'g'  => '%l',
            'h'  => '%I',
            'H'  => '%H',
            'i'  => '%M',
            's'  => '%S',
            // Timezone - no strf eq : e, I, P, Z
            'O'  => '%z',
            'T'  => '%Z',
            // Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
            'U'  => '%s',
        ];
    }

    private static function getStrftimeToDateFormats()
    {
        return [
            // Day
            '%d'  => 'd',
            '%a'  => 'D',
            '%#d' => 'j',
            '%A'  => 'l',
            '%u'  => 'N',
            '%w'  => 'w',
            '%j'  => 'z',
            // Week
            '%V'  => 'W',
            // Month
            '%B'  => 'F',
            '%m'  => 'm',
            '%b'  => 'M',
            // Year
            '%G'  => 'o',
            '%Y'  => 'Y',
            '%y'  => 'y',
            // Time
            '%P'  => 'a',
            '%p'  => 'A',
            '%l'  => 'g',
            '%I'  => 'h',
            '%H'  => 'H',
            '%M'  => 'i',
            '%S'  => 's',
            // Timezone
            '%z'  => 'O',
            '%Z'  => 'T',
            // Full Date / Time
            '%s'  => 'U',
        ];
    }
}
