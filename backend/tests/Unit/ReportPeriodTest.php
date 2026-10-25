<?php

namespace Tests\Unit;

use App\Enums\ReportPreset;
use App\Support\ReportPeriod;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class ReportPeriodTest extends TestCase
{
    private function now(): CarbonImmutable
    {
        return CarbonImmutable::parse('2026-10-15 12:00:00');
    }

    public function test_this_month_runs_until_today_and_compares_with_the_same_days_of_last_month(): void
    {
        $period = ReportPeriod::resolve(ReportPreset::ThisMonth, now: $this->now());

        $this->assertSame('2026-10-01', $period->start->toDateString());
        $this->assertSame('2026-10-16', $period->end->toDateString()); // exclusivo: incluye todo el día 15
        $this->assertSame('2026-09-01', $period->previousStart->toDateString());
        $this->assertSame('2026-09-16', $period->previousEnd->toDateString());
        $this->assertSame('Octubre 2026', $period->label());
    }

    public function test_last_month_is_the_whole_month_and_compares_with_the_one_before(): void
    {
        $period = ReportPeriod::resolve(ReportPreset::LastMonth, now: $this->now());

        $this->assertSame('2026-09-01', $period->start->toDateString());
        $this->assertSame('2026-10-01', $period->end->toDateString());
        $this->assertSame('2026-08-01', $period->previousStart->toDateString());
        // Agosto tiene 31 días y septiembre 30: el anterior no se pasa del inicio del actual
        $this->assertSame('2026-09-01', $period->previousEnd->toDateString());
    }

    public function test_this_year_and_last_year(): void
    {
        $thisYear = ReportPeriod::resolve(ReportPreset::ThisYear, now: $this->now());
        $this->assertSame('2026-01-01', $thisYear->start->toDateString());
        $this->assertSame('2025-01-01', $thisYear->previousStart->toDateString());
        $this->assertSame('2025-10-16', $thisYear->previousEnd->toDateString());

        $lastYear = ReportPeriod::resolve(ReportPreset::LastYear, now: $this->now());
        $this->assertSame('2025-01-01', $lastYear->start->toDateString());
        $this->assertSame('2026-01-01', $lastYear->end->toDateString());
        $this->assertSame('2024-01-01', $lastYear->previousStart->toDateString());
        $this->assertSame('2025-01-01', $lastYear->previousEnd->toDateString());
        $this->assertSame('2025', $lastYear->label());
    }

    public function test_custom_range_compares_with_the_range_of_equal_length_right_before(): void
    {
        $period = ReportPeriod::resolve(
            ReportPreset::Custom,
            CarbonImmutable::parse('2026-03-10'),
            CarbonImmutable::parse('2026-03-19'),
            $this->now(),
        );

        $this->assertSame('2026-03-10', $period->start->toDateString());
        $this->assertSame('2026-03-20', $period->end->toDateString()); // 10 días: del 10 al 19
        $this->assertSame('2026-02-28', $period->previousStart->toDateString());
        $this->assertSame('2026-03-10', $period->previousEnd->toDateString());
        $this->assertSame('10/03/2026 – 19/03/2026', $period->label());
    }
}
