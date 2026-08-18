<?php

function calculateInterest(float $principal, float $rate, string $period): array
{
    $rateDecimal = $rate / 100;

    switch ($period) {

        case 'daily':
            $dailyInterest = $principal * $rateDecimal;
            $monthlyInterest = $dailyInterest * 30;
            $yearlyInterest = $dailyInterest * 365;
            break;

        case 'monthly':
            $monthlyInterest = $principal * $rateDecimal;
            $dailyInterest = $monthlyInterest / 30;
            $yearlyInterest = $monthlyInterest * 12;
            break;

        case 'yearly':
            $yearlyInterest = $principal * $rateDecimal;
            $monthlyInterest = $yearlyInterest / 12;
            $dailyInterest = $yearlyInterest / 365;
            break;

        default:
            $dailyInterest = 0;
            $monthlyInterest = 0;
            $yearlyInterest = 0;
    }

    return [
        'daily' => $dailyInterest,
        'monthly' => $monthlyInterest,
        'yearly' => $yearlyInterest
    ];
}
function calculateElapsedDays(string $startDate, ?string $endDate = null): int
{
    $start = new DateTime($startDate);

    if ($endDate === null) {
        $end = new DateTime();
    } else {
        $end = new DateTime($endDate);
    }

    $difference = $start->diff($end);

    return $difference->days;
}

function calculateAccruedInterest(
    float $principal,
    float $rate,
    string $period,
    int $elapsedDays
): float {

    $rateDecimal = $rate / 100;

    switch ($period) {

        case 'daily':
            return $principal * $rateDecimal * $elapsedDays;

        case 'monthly':
            $dailyRate = $rateDecimal / 30;
            return $principal * $dailyRate * $elapsedDays;

        case 'yearly':
            $dailyRate = $rateDecimal / 365;
            return $principal * $dailyRate * $elapsedDays;

        default:
            return 0;
    }
}