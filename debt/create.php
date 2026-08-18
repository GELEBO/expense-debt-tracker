<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Create Debt
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $creditor = trim($_POST['creditor'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $original_amount = $_POST['original_amount'] ?? '';
    $interest_rate = $_POST['interest_rate'] ?? 0;
    $interest_period = $_POST['interest_period'] ?? 'monthly';
    $interest_start_date = $_POST['interest_start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Basic Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($creditor) ||
        empty($original_amount) ||
        empty($interest_start_date) ||
        empty($due_date)
    ) {
        die("Please fill in all required fields.");
    }

    if (!is_numeric($original_amount) || $original_amount <= 0) {
        die("Please enter a valid debt amount.");
    }

    if (!is_numeric($interest_rate) || $interest_rate < 0) {
        die("Please enter a valid interest rate.");
    }

    $allowed_periods = ['daily', 'monthly', 'yearly'];

    if (!in_array($interest_period, $allowed_periods, true)) {
        die("Please select a valid interest period.");
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Debt
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO debts
        (
            user_id,
            creditor,
            description,
            original_amount,
            interest_rate,
            interest_period,
            interest_start_date,
            due_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        1,
        $creditor,
        $description,
        $original_amount,
        $interest_rate,
        $interest_period,
        $interest_start_date,
        $due_date,
        'active'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Return to Debt List
    |--------------------------------------------------------------------------
    */

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Debt | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Add Debt</h1>

    <p>
        <a href="../index.php">
            ← Back to Dashboard
        </a>
    </p>

    <form method="POST">

        <div>

            <label for="creditor">
                Creditor:
            </label>

            <input
                type="text"
                id="creditor"
                name="creditor"
                required
            >

        </div>

        <br>

        <div>

            <label for="description">
                Description:
            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
                cols="40"
            ></textarea>

        </div>

        <br>

        <div>

            <label for="original_amount">
                Original Amount:
            </label>

            <input
                type="number"
                id="original_amount"
                name="original_amount"
                step="0.01"
                min="0.01"
                required
            >

        </div>

        <br>

        <div>

            <label for="interest_rate">
                Interest Rate (%):
            </label>

            <input
                type="number"
                id="interest_rate"
                name="interest_rate"
                step="0.01"
                min="0"
                value="0"
                required
            >

        </div>

        <br>

        <div>

            <label for="interest_period">
                Interest Period:
            </label>

            <select
                id="interest_period"
                name="interest_period"
                required
            >
                <option value="daily">Daily</option>
                <option value="monthly" selected>Monthly</option>
                <option value="yearly">Yearly</option>
            </select>

        </div>

        <br>

        <div>

            <label for="interest_start_date">
                Interest Start Date:
            </label>

            <input
                type="date"
                id="interest_start_date"
                name="interest_start_date"
                required
            >

        </div>

        <br>

        <div>

            <label for="due_date">
                Due Date:
            </label>

            <input
                type="date"
                id="due_date"
                name="due_date"
                required
            >

        </div>

        <br>

        <button type="submit">
            Save Debt
        </button>

    </form>

</body>

</html>