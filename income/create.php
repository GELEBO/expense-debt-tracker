<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Create Income
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $income_date = $_POST['income_date'] ?? '';
    $source = trim($_POST['source'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($income_date) ||
        empty($source) ||
        empty($amount)
    ) {
        die("Please fill in all required fields.");
    }

    if (!is_numeric($amount) || $amount <= 0) {
        die("Please enter a valid income amount.");
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Income
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO income
        (
            user_id,
            income_date,
            source,
            amount,
            description
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        1,
        $income_date,
        $source,
        $amount,
        $description
    ]);

    /*
    |--------------------------------------------------------------------------
    | Return to Income List
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

    <title>Add Income | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Add Income</h1>

    <p>
        <a href="../index.php">
            ← Back to Dashboard
        </a>
    </p>

    <form method="POST">

        <div>

            <label for="income_date">
                Date:
            </label>

            <input
                type="date"
                id="income_date"
                name="income_date"
                value="<?php echo date('Y-m-d'); ?>"
                required
            >

        </div>

        <br>

        <div>

            <label for="source">
                Source:
            </label>

            <input
                type="text"
                id="source"
                name="source"
                placeholder="e.g. Salary"
                required
            >

        </div>

        <br>

        <div>

            <label for="amount">
                Amount:
            </label>

            <input
                type="number"
                id="amount"
                name="amount"
                step="0.01"
                min="0.01"
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

        <button type="submit">
            Save Income
        </button>

    </form>

</body>

</html>