<?php

namespace App\Services;

use App\Models\Sport;
use Carbon\CarbonInterface;

class BookingPricer
{
    /**
     * Peak = weekends, or weekday evenings from 5:00 PM onward.
     */
    public function isPeak(CarbonInterface $start): bool
    {
        return $start->isWeekend() || $start->hour >= 17;
    }

    /**
     * The applicable hourly rate for a sport at the given start time.
     */
    public function priceFor(Sport $sport, CarbonInterface $start): string
    {
        return $this->isPeak($start) ? $sport->rate_peak : $sport->rate_offpeak;
    }
}
