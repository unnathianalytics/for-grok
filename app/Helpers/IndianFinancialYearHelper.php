<?php

if (!function_exists('fin_year')) {
    /**
     * Returns the Indian financial year dates based on the current date.
     *
     * @return array An array containing 'from_date' and 'to_date' in 'Y-m-d' format.
     */
    function fin_year(): array
    {
        $currentDate = new DateTime();
        $currentMonth = (int) $currentDate->format('m');
        $currentYear = (int) $currentDate->format('Y');

        // If current date is before April, use previous year as start of financial year
        $fromYear = ($currentMonth < 4) ? $currentYear - 1 : $currentYear;

        $toYear = $fromYear + 1;

        return [
            'from_date' => sprintf('%d-04-01', $fromYear),
            'to_date' => sprintf('%d-03-31', $toYear),
        ];
    }
}
