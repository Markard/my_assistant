<?php namespace MyAssistant\CoreBundle\Helpers;

use MyAssistant\CoreBundle\Exception\InvalidPropertyException;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Request;

class DateHelper
{
    /**
     * @param Request $request
     * @param $name
     *
     * @throws InvalidPropertyException
     *
     * @return Carbon|null
     */
    public static function getMonthDateFromRequest(Request $request, $name)
    {
        if ($date = $request->query->get($name)) {
            try {
                list($year, $month) = explode('-', $date);
                $date = Carbon::create((int) $year, (int) $month, 1);
            } catch (\Exception $e) {
                throw new InvalidPropertyException('Invalid ' . $name
                    . ' parameter. Date format should be Y-m format.');
            }
        }

        return $date;
    }
}