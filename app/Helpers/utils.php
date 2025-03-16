<?php
/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

// Get web URL
if (!function_exists('getWebURL')) {
    function getWebURL()
    {
        // return 'http://192.168.151.238/tulipap/public';
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    }
}

// Get APIs URL
if (!function_exists('getApiURL')) {
    function getApiURL()
    {
        return (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api';
    }
}

// Get APIs URL
if (!function_exists('isVideoFile')) {
    function isVideoFile($url)
    {
        // Extract file extension
        $pathInfo = pathinfo($url);
        $extension = strtolower($pathInfo['extension'] ?? '');

        // List of recognized video extensions
        $videoExtensions = ['mp4', 'mkv', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mpeg'];

        // Check if the extension is in the list
        return in_array($extension, $videoExtensions);
    }
}

// Friendly username from names
if (!function_exists("friendlyUsername")) {
    function friendlyUsername($str)
    {
        // convert to entities
        $string = htmlentities($str, ENT_QUOTES, 'UTF-8');
        // regex to convert accented chars into their closest a-z ASCII equivelent
        $string = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', $string);
        // convert back from entities
        $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
        // any straggling characters that are not strict alphanumeric are replaced with an underscore
        $string = preg_replace('~[^0-9a-z]+~i', '_', $string);
        // trim / cleanup / all lowercase
        $string = trim($string, '-');
        $string = strtolower($string);

        return $string;
    }
}

// Transform mentions and hashtag to URL
if (!function_exists('transformMentionHashtag')) {
    function transformMentionHashtag($web_url, $subject)
    {
        $pat = array('/#(\w+)/', '/@(\w+)/');
        $rep = array('<a href="' . $web_url . '/trends/$1" class="text-success">#$1</a>', '<a href="' . $web_url . '/$1">@$1</a>');

        return preg_replace($pat, $rep, $subject);
    }
}

// Get all hashtags from text
if (!function_exists('getHashtags')) {
    function getHashtags($subject)
    {
        $hashtags = false;

        preg_match_all('/#(\w+)/u', $subject, $matches);

        if ($matches) {
            $matches[0] = str_replace('#', '', $matches[0]); //replace #
            $hashtags = implode(' ,', $matches[0]);
        }

        return trim(explode(' ,', $hashtags)[0]) != null ? explode(' ,', $hashtags) : [];
    }
}

// Get all mentions from text
if (!function_exists('getMentions')) {
    function getMentions($subject)
    {
        $mentions = false;

        preg_match_all('/@(\w+)/u', $subject, $matches);

        if ($matches) {
            $matches[0] = str_replace('@', '', $matches[0]); //replace @
            $mentions = implode(' ,', $matches[0]);
        }

        return trim(explode(' ,', $mentions)[0]) != null ? explode(' ,', $mentions) : [];
    }
}

// Check if a value exists into an multidimensional array
if (!function_exists('inArrayR')) {
    function inArrayR($needle, $haystack, $key)
    {
        return in_array($needle, collect($haystack)->pluck($key)->toArray());
    }
}

// Get array of columns from a keys/values object
if (!function_exists('getArrayKeys')) {
    function getArrayKeys($haystack, $ref)
    {
        return collect($haystack)->pluck($ref)->toArray();
    }
}

// Get hours difference between two dates
if (!function_exists('hoursDifference')) {
    function hoursDifference($dateMin, $dateMax)
    {
        $hoursDifference = $dateMax->diffInHours($dateMin, false);

        return abs($hoursDifference);
    }
}

// Month fully readable
if (!function_exists('explicitMonth')) {
    function explicitMonth($month)
    {
        setlocale(LC_ALL, app()->getLocale());

        return utf8_encode(strftime("%B", strtotime(date('F', mktime(0, 0, 0, $month, 10)))));
    }
}

// Day and month fully readable
if (!function_exists('explicitDayMonth')) {
    function explicitDayMonth($date)
    {
        $locale = app()->getLocale();
        $currentDate = Carbon::parse($date);
        $format = $locale === 'fr' ? 'j F' : 'F j';

        Carbon::setlocale($locale);

        return $currentDate->translatedFormat($format);
    }
}

// Date fully readable
if (!function_exists('explicitDate')) {
    function explicitDate($date)
    {
        $locale = app()->getLocale();
        $currentDate = Carbon::parse($date);
        $format = $locale === 'fr' ? 'D j F Y à H:i' : 'D, F j, Y g:i A';

        Carbon::setlocale($locale);

        return $currentDate->translatedFormat($format);
    }
}

// Date/Time fully readable
if (!function_exists('explicitDateTime')) {
    function explicitDateTime($date)
    {
        $locale = app()->getLocale();
        $currentDate = Carbon::parse($date);
        $format = $locale === 'fr' ? 'j M Y à H:i' : 'M j, Y \a\t g:i A';

        Carbon::setlocale($locale);

        return $currentDate->translatedFormat($format);
    }
}

// Get start and end of specific week in month
if (!function_exists('getStartAndEndOfWeekInMonth')) {
    function getStartAndEndOfWeekInMonth($year, $month, $weekNumber)
    {
        // Creates a DateTime object for the first day of the month
        $startOfMonth = new DateTime("$year-$month-01");

        // Find the first monday of the month
        $startOfMonth->modify('monday this week');
        if ($startOfMonth->format('n') != $month) {
            $startOfMonth->modify('next monday');
        }

        // Calculate the start of the specific week
        $startOfWeek = clone $startOfMonth;

        $startOfWeek->modify('+' . ($weekNumber - 1) . ' weeks');

        // Calculate the end of the specific week
        $endOfWeek = clone $startOfWeek;

        $endOfWeek->modify('sunday this week');

        // Adjust to not exceed the month
        if ($startOfWeek->format('n') != $month) {
            $startOfWeek->modify('first day of next month');
            $startOfWeek->modify('-1 week');

            $endOfWeek = clone $startOfWeek;

            $endOfWeek->modify('sunday this week');
        }

        // Make sure the end of the week does not exceed the end of the month
        $endOfMonth = new DateTime("$year-$month-" . date('t', strtotime("$year-$month-01")));

        if ($endOfWeek > $endOfMonth) {
            $endOfWeek = $endOfMonth;
        }

        return [
            'start' => $startOfWeek->format('Y-m-d'),
            'end' => $endOfWeek->format('Y-m-d')
        ];
    }
}

// All weeks of specific month
if (!function_exists('getWeeksOfMonth')) {
    function getWeeksOfMonth($year, $month)
    {
        $weeks = [];
        // Start and end of the month
        $startDate = new DateTime("$year-$month-01");
        $endDate = (clone $startDate)->modify('last day of this month');
        // First week initialization
        $startOfWeek = clone $startDate;

        $startOfWeek->modify('this week');

        while ($startOfWeek <= $endDate) {
            $endOfWeek = clone $startOfWeek;

            $endOfWeek->modify('sunday this week');

            // Adjustment to not exceed the end of the month
            if ($endOfWeek > $endDate) {
                $endOfWeek = $endDate;
            }

            // Adding the week to the list
            $weeks[] = [
                'start' => $startOfWeek->format('Y-m-d'),
                'end' => $endOfWeek->format('Y-m-d'),
            ];

            // Moving on to the next week
            $startOfWeek->modify('next week');
        }

        return $weeks;
    }
}

// All quarters of specific year
if (!function_exists('getQuarterDates')) {
    function getQuarterDates($year, $quarter)
    {
        switch ($quarter) {
            case 1:
                $startDate = "$year-01-01";
                $endDate = "$year-03-31";
                break;
            case 2:
                $startDate = "$year-04-01";
                $endDate = "$year-06-30";
                break;
            case 3:
                $startDate = "$year-07-01";
                $endDate = "$year-09-30";
                break;
            case 4:
                $startDate = "$year-10-01";
                $endDate = "$year-12-31";
                break;
            default:
                throw new Exception('Invalid quarter');
        }

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }
}

// All half-yearly of specific year
if (!function_exists('getHalfYearDates')) {
    function getHalfYearDates($year, $portion)
    {
        switch ($portion) {
            case 1:
                $startDate = "$year-01-01";
                $endDate = "$year-06-30";
                break;
            case 2:
                $startDate = "$year-07-01";
                $endDate = "$year-12-31";
                break;
            default:
                throw new Exception('Invalid portion');
        }

        return [
            'start' => $startDate,
            'end' => $endDate
        ];
    }
}

// Delete item from exploded array
if (!function_exists('deleteExplodedArrayItem')) {
    function deleteExplodedArrayItem($separator, $subject, $item)
    {
        $explodes = explode($separator, $subject);
        $clean_inventory = array();

        foreach ($explodes as $explode) {
            if (!isset($clean_inventory[$explode])) {
                $clean_inventory[$explode] = 0;
            }

            $clean_inventory[$explode]++;
        }

        // Item can be deleted
        unset($clean_inventory[$item]);

        $saved = array();

        foreach ($clean_inventory as $key => $quantity) {
            $saved = array_merge($saved, array_fill(0, $quantity, $key));
        }

        return implode($separator, $saved);
    }
}

// Add an item to exploded array
if (!function_exists('addItemsToExplodedArray')) {
    function addItemsToExplodedArray($separator, $subject, $items)
    {
        $explodes = explode($separator, $subject);
        $saved = array_merge($explodes, $items);

        return implode($separator, $saved);
    }
}

// Get columns of a same namme from JSON
if (!function_exists('getColumnsFromJson')) {
    function getColumnsFromJson($jsonData, $column)
    {
        $jsonString = json_encode($jsonData);
        $data = json_decode($jsonString, true);
        $tableColumns = [];

        foreach ($data as $table) {
            if (isset($table[$column])) {
                $tableColumns[] = $table[$column];
            }
        }

        return $tableColumns;
    }
}

// Paginate an array
if (!function_exists('paginate')) {
    function paginate(array $items, int $perPage = 5, ?int $page = null, $options = [])
    {
        $page = $page ?: (LengthAwarePaginator::resolveCurrentPage() ?: 1);
        $items = collect($items);

        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}
