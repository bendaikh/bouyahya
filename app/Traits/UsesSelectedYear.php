<?php

namespace App\Traits;

use Carbon\Carbon;

trait UsesSelectedYear
{
    /**
     * Get the selected year from session, default to current year
     */
    protected function getSelectedYear()
    {
        return session('selected_year', Carbon::now()->year);
    }

    /**
     * Apply year filter to a query based on a date column
     */
    protected function applyYearFilter($query, $dateColumn = 'date')
    {
        $year = $this->getSelectedYear();
        return $query->whereYear($dateColumn, $year);
    }
}
