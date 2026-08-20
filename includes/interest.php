<?php

/*
|--------------------------------------------------------------------------
| Interest Calculation Helpers
|--------------------------------------------------------------------------
*/

/**
 * Calculate equivalent daily, monthly, and yearly interest
 * for the current principal.
 */
function calculateInterest(
    float $principal,
    float $rate,
    string $period
): array {

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


/**
 * Calculate the number of elapsed days between two dates.
 */
function calculateElapsedDays(
    string $startDate,
    ?string $endDate = null
): int {

    $start = new DateTime($startDate);

    if ($endDate === null) {
        $end = new DateTime();
    } else {
        $end = new DateTime($endDate);
    }

    $difference = $start->diff($end);

    return $difference->days;
}


/**
 * Calculate interest accumulated during a specific number of days.
 *
 * Interest is always calculated from the CURRENT principal.
 */
function calculateAccruedInterest(
    float $principal,
    float $rate,
    string $period,
    int $elapsedDays
): float {

    if ($principal <= 0 || $elapsedDays <= 0) {
        return 0;
    }

    $rateDecimal = $rate / 100;

    switch ($period) {

        case 'daily':
            $dailyRate = $rateDecimal;
            break;

        case 'monthly':
            $dailyRate = $rateDecimal / 30;
            break;

        case 'yearly':
            $dailyRate = $rateDecimal / 365;
            break;

        default:
            return 0;
    }

    return $principal * $dailyRate * $elapsedDays;
}


/*
|--------------------------------------------------------------------------
| Debt Calculation Engine
|--------------------------------------------------------------------------
|
| This is the central engine for the entire debt system.
|
| Rules:
|
| 1. Interest is calculated only on the current principal.
| 2. Interest accumulates between payment events.
| 3. Payments are applied to accrued interest first.
| 4. Remaining payment is applied to principal.
| 5. Interest calculation continues using the new principal.
| 6. Payment history must be processed chronologically.
|
|--------------------------------------------------------------------------
*/


/**
 * Calculate the complete current state of a debt.
 *
 * Expected payment format:
 *
 * [
 *     [
 *         'payment_date' => '2026-08-17',
 *         'amount'       => 500,
 *         'note'         => 'Partial payment'
 *     ],
 *     ...
 * ]
 *
 * Payments must contain a payment date and amount.
 */
function calculateDebtBalance(
    float $originalPrincipal,
    float $rate,
    string $period,
    string $interestStartDate,
    array $payments = [],
    ?string $calculationDate = null
): array {

    /*
    |--------------------------------------------------------------------------
    | Initial values
    |--------------------------------------------------------------------------
    */

    $principal = $originalPrincipal;

    $accruedInterest = 0;

    $totalPaid = 0;

    $totalInterestAccrued = 0;

    $paymentHistory = [];

    /*
    |--------------------------------------------------------------------------
    | Calculation date
    |--------------------------------------------------------------------------
    */

    if ($calculationDate === null) {
        $calculationDate = date('Y-m-d');
    }

    /*
    |--------------------------------------------------------------------------
    | Sort payments chronologically
    |--------------------------------------------------------------------------
    */

    usort($payments, function ($a, $b) {

        return strtotime($a['payment_date'])
            <=> strtotime($b['payment_date']);
    });

    /*
    |--------------------------------------------------------------------------
    | Previous event date
    |--------------------------------------------------------------------------
    */

    $previousDate = $interestStartDate;


    /*
    |--------------------------------------------------------------------------
    | Process every payment
    |--------------------------------------------------------------------------
    */

    foreach ($payments as $payment) {

        $paymentDate = $payment['payment_date'];

        $paymentAmount = (float) $payment['amount'];

        /*
        |--------------------------------------------------------------------------
        | Ignore invalid payments
        |--------------------------------------------------------------------------
        */

        if ($paymentAmount <= 0) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | Do not process payments after calculation date
        |--------------------------------------------------------------------------
        */

        if (
            strtotime($paymentDate)
            > strtotime($calculationDate)
        ) {
            continue;
        }

        /*
        |--------------------------------------------------------------------------
        | If principal is already zero, debt is fully paid.
        |--------------------------------------------------------------------------
        */

        if ($principal <= 0 && $accruedInterest <= 0) {
            break;
        }

        /*
        |--------------------------------------------------------------------------
        | Calculate elapsed days
        |--------------------------------------------------------------------------
        */

        $elapsedDays = calculateElapsedDays(
            $previousDate,
            $paymentDate
        );

        /*
        |--------------------------------------------------------------------------
        | Accrue interest on CURRENT principal
        |--------------------------------------------------------------------------
        */

        $interestForPeriod = calculateAccruedInterest(
            $principal,
            $rate,
            $period,
            $elapsedDays
        );

        $accruedInterest += $interestForPeriod;

        $totalInterestAccrued += $interestForPeriod;

        /*
        |--------------------------------------------------------------------------
        | Payment allocation
        |--------------------------------------------------------------------------
        */

        $interestPaid = min(
            $paymentAmount,
            $accruedInterest
        );

        $remainingPayment = $paymentAmount - $interestPaid;

        $principalPaid = min(
            $remainingPayment,
            $principal
        );

        /*
        |--------------------------------------------------------------------------
        | Update balances
        |--------------------------------------------------------------------------
        */

        $accruedInterest -= $interestPaid;

        $principal -= $principalPaid;

        $totalPaid += $interestPaid + $principalPaid;

        /*
        |--------------------------------------------------------------------------
        | Record payment result
        |--------------------------------------------------------------------------
        */

        $paymentHistory[] = [

            'payment_date' => $paymentDate,

            'payment_amount' => $paymentAmount,

            'elapsed_days' => $elapsedDays,

            'interest_accrued' => $interestForPeriod,

            'interest_paid' => $interestPaid,

            'principal_paid' => $principalPaid,

            'remaining_principal' => $principal,

            'remaining_interest' => $accruedInterest,

            'note' => $payment['note'] ?? ''
        ];

        /*
        |--------------------------------------------------------------------------
        | Move to next event
        |--------------------------------------------------------------------------
        */

        $previousDate = $paymentDate;
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate interest from the last event until calculation date
    |--------------------------------------------------------------------------
    */

    if ($principal > 0) {

        $elapsedDays = calculateElapsedDays(
            $previousDate,
            $calculationDate
        );

        $interestForCurrentPeriod = calculateAccruedInterest(
            $principal,
            $rate,
            $period,
            $elapsedDays
        );

        $accruedInterest += $interestForCurrentPeriod;

        $totalInterestAccrued += $interestForCurrentPeriod;

    } else {

        $elapsedDays = 0;
    }


    /*
    |--------------------------------------------------------------------------
    | Total currently owed
    |--------------------------------------------------------------------------
    */

    $totalOwed = $principal + $accruedInterest;


    /*
    |--------------------------------------------------------------------------
    | Determine status
    |--------------------------------------------------------------------------
    */

    if ($totalOwed <= 0.00001) {

        $status = 'paid';

    } elseif ($totalPaid > 0) {

        $status = 'partially_paid';

    } else {

        $status = 'unpaid';
    }


    /*
    |--------------------------------------------------------------------------
    | Return complete debt calculation
    |--------------------------------------------------------------------------
    */

    return [

        'original_principal' => $originalPrincipal,

        'principal' => max(0, $principal),

        'accrued_interest' => max(0, $accruedInterest),

        'total_owed' => max(0, $totalOwed),

        'total_paid' => $totalPaid,

        'total_interest_accrued' => $totalInterestAccrued,

        'status' => $status,

        'calculation_date' => $calculationDate,

        'payment_history' => $paymentHistory
    ];
}