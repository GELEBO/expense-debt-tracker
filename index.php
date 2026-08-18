<?php

require_once "config/database.php";

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total_expenses FROM expenses");
$totalExpenses = $stmt->fetch(PDO::FETCH_ASSOC)['total_expenses'];

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total_income FROM income");
$totalIncome = $stmt->fetch(PDO::FETCH_ASSOC)['total_income'];

$currentBalance = $totalIncome - $totalExpenses;

$stmt = $pdo->query("SELECT COALESCE(SUM(original_amount), 0) AS total_debt FROM debts");
$totalDebt = $stmt->fetch(PDO::FETCH_ASSOC)['total_debt'];

$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total_paid FROM debt_payments");
$totalPaid = $stmt->fetch(PDO::FETCH_ASSOC)['total_paid'];

$remainingDebt = $totalDebt - $totalPaid;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense & Debt Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<h1>Expense & Debt Tracker</h1>

<p class="dashboard-intro">
    Welcome to your financial dashboard.
</p>
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

<div class="summary-grid">

    <div class="summary-card">
        <h2>Total Income</h2>
        <p><?php echo number_format($totalIncome, 2); ?> ETB</p>
    </div>

    <div class="summary-card">
        <h2>Total Expenses</h2>
        <p><?php echo number_format($totalExpenses, 2); ?> ETB</p>
    </div>

    <div class="summary-card">
        <h2>Current Balance</h2>
        <p><?php echo number_format($currentBalance, 2); ?> ETB</p>
    </div>

    <div class="summary-card">
        <h2>Total Debt</h2>
        <p><?php echo number_format($totalDebt, 2); ?> ETB</p>
    </div>

    <div class="summary-card">
        <h2>Total Debt Paid</h2>
        <p><?php echo number_format($totalPaid, 2); ?> ETB</p>
    </div>

    <div class="summary-card">
        <h2>Remaining Debt</h2>
        <p><?php echo number_format($remainingDebt, 2); ?> ETB</p>
    </div>

</div>


</body>
</html>