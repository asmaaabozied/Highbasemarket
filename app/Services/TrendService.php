<?php

namespace App\Services;

use App\Enum\TrendAggregate;
use App\Enum\TrendPeriod;
use Carbon\CarbonInterface;
use Flowframe\Trend\Trend;
use Illuminate\Database\Eloquent\Builder;

class TrendService
{
    public function build(
        Builder $query,
        TrendPeriod $period,
        TrendAggregate $aggregate,
        string $column,
        CarbonInterface $from,
        CarbonInterface $to
    ) {
        $trend = Trend::query($query)
            ->between(start: $from, end: $to)
            ->{$period->method()}();

        return match ($aggregate) {
            TrendAggregate::SUM   => $trend->sum($column),
            TrendAggregate::AVG   => $trend->avg($column),
            TrendAggregate::COUNT => $trend->count($column),
            TrendAggregate::MAX   => $trend->max($column),
            TrendAggregate::MIN   => $trend->min($column),
        };
    }
}
