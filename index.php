<?php

require_once "includes/auth.php";
require_once "config/database.php";
require_once "includes/interest.php";

$userId = $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Income
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total_income
    FROM income
    WHERE user_id = :user_id
");

$stmt->execute([
    ':user_id' => $userId
]);

$totalIncome = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total_income'];


/*
|--------------------------------------------------------------------------
| Expenses
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0) AS total_expenses
    FROM expenses
    WHERE user_id = :user_id
");

$stmt->execute([
    ':user_id' => $userId
]);

$totalExpenses = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'];


$currentBalance = $totalIncome - $totalExpenses;


/*
|--------------------------------------------------------------------------
| Debt Calculations
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        original_amount,
        interest_rate,
        interest_period,
        interest_start_date
    FROM debts
    WHERE user_id = :user_id
");

$stmt->execute([
    ':user_id' => $userId
]);

$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);


$totalDebt = 0;
$totalPaid = 0;
$remainingPrincipal = 0;
$totalAccruedInterest = 0;
$totalAmountOwed = 0;


foreach ($debts as $debt) {

    $originalAmount = (float) $debt['original_amount'];


    /*
    |--------------------------------------------------------------------------
    | Debt Payments
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0)
        FROM debt_payments
        WHERE debt_id = :debt_id
    ");

    $stmt->execute([
        ':debt_id' => $debt['id']
    ]);

    $paid = (float) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Remaining Principal
    |--------------------------------------------------------------------------
    */

    $remaining = max(
        0,
        $originalAmount - $paid
    );


    /*
    |--------------------------------------------------------------------------
    | Interest Calculation
    |--------------------------------------------------------------------------
    */

    $elapsedDays = calculateElapsedDays(
        $debt['interest_start_date']
    );


    $accruedInterest = calculateAccruedInterest(
        $remaining,
        (float) $debt['interest_rate'],
        $debt['interest_period'],
        $elapsedDays
    );


    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

    $totalDebt += $originalAmount;

    $totalPaid += $paid;

    $remainingPrincipal += $remaining;

    $totalAccruedInterest += $accruedInterest;
}


$totalAmountOwed =
    $remainingPrincipal + $totalAccruedInterest;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>

<body>

    <!-- =========================
         DASHBOARD HEADER
         ========================= -->

    <div class="dashboard-header">

        <div class="dashboard-title">

            <h1>
                Expense & Debt Tracker
            </h1>

            <p class="dashboard-intro">
                Welcome to your financial dashboard.
            </p>

        </div>

        <div class="user-actions">

            <a
                href="users/logout.php"
                class="logout-btn"
            >
                Logout
            </a>

        </div>

    </div>


    <!-- =========================
         FINANCIAL SUMMARY
         ========================= -->

    <div class="summary-grid">

        <div class="summary-card">

            <h2>Total Income</h2>

            <p>
                <?php
                echo number_format(
                    $totalIncome,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Total Expenses</h2>

            <p>
                <?php
                echo number_format(
                    $totalExpenses,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Current Balance</h2>

            <p>
                <?php
                echo number_format(
                    $currentBalance,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Total Original Debt</h2>

            <p>
                <?php
                echo number_format(
                    $totalDebt,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Total Debt Paid</h2>

            <p>
                <?php
                echo number_format(
                    $totalPaid,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Remaining Principal</h2>

            <p>
                <?php
                echo number_format(
                    $remainingPrincipal,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Accrued Interest</h2>

            <p>
                <?php
                echo number_format(
                    $totalAccruedInterest,
                    2
                );
                ?>
                ETB
            </p>

        </div>


        <div class="summary-card">

            <h2>Total Amount Owed</h2>

            <p>
                <?php
                echo number_format(
                    $totalAmountOwed,
                    2
                );
                ?>
                ETB
            </p>

        </div>

    </div>


    <!-- =========================
         DASHBOARD NAVIGATION
         ========================= -->

    <nav class="dashboard-nav">

        <a href="income/index.php">
            Income
        </a>

        <a href="expenses/index.php">
            Expenses
        </a>

        <a href="debt/index.php">
            Debts
        </a>

    </nav>

</body>

</html>