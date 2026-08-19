<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Get Debt ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid debt ID.");
}

/*
|--------------------------------------------------------------------------
| Fetch Debt
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        creditor,
        description,
        original_amount,
        interest_rate,
        interest_period,
        interest_start_date,
        due_date,
        status
    FROM debts
    WHERE id = ?
");

$stmt->execute([$id]);

$debt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

/*
|--------------------------------------------------------------------------
| Update Debt
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $creditor = trim($_POST['creditor'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $original_amount = $_POST['original_amount'] ?? '';
    $interest_rate = $_POST['interest_rate'] ?? 0;
    $interest_period = $_POST['interest_period'] ?? '';
    $interest_start_date = $_POST['interest_start_date'] ?? '';
    $due_date = $_POST['due_date'] ?? '';
    $status = $_POST['status'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($creditor) ||
        empty($original_amount) ||
        empty($interest_period) ||
        empty($interest_start_date) ||
        empty($due_date) ||
        empty($status)
    ) {
        die("Please fill in all required fields.");
    }

    if (!is_numeric($original_amount) || $original_amount <= 0) {
        die("Please enter a valid debt amount.");
    }

    if (!is_numeric($interest_rate) || $interest_rate < 0) {
        die("Please enter a valid interest rate.");
    }

    if (!in_array($interest_period, ['daily', 'monthly', 'yearly'], true)) {
        die("Invalid interest period.");
    }

    /*
    |--------------------------------------------------------------------------
    | Update Debt
    |--------------------------------------------------------------------------
    */

    $updateStmt = $pdo->prepare("
        UPDATE debts
        SET
            creditor = ?,
            description = ?,
            original_amount = ?,
            interest_rate = ?,
            interest_period = ?,
            interest_start_date = ?,
            due_date = ?,
            status = ?
        WHERE id = ?
    ");

    $updateStmt->execute([
        $creditor,
        $description,
        $original_amount,
        $interest_rate,
        $interest_period,
        $interest_start_date,
        $due_date,
        $status,
        $id
    ]);

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

    <title>Edit Debt | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Edit Debt</h1>

    <p>
        <a href="index.php">
            ← Back to Debts
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
                value="<?php echo htmlspecialchars($debt['creditor']); ?>"
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
            ><?php echo htmlspecialchars($debt['description']); ?></textarea>

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
                value="<?php echo htmlspecialchars($debt['original_amount']); ?>"
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
                value="<?php echo htmlspecialchars($debt['interest_rate']); ?>"
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

                <option
                    value="daily"
                    <?php
                    if ($debt['interest_period'] === 'daily') {
                        echo 'selected';
                    }
                    ?>
                >
                    Daily
                </option>

                <option
                    value="monthly"
                    <?php
                    if ($debt['interest_period'] === 'monthly') {
                        echo 'selected';
                    }
                    ?>
                >
                    Monthly
                </option>

                <option
                    value="yearly"
                    <?php
                    if ($debt['interest_period'] === 'yearly') {
                        echo 'selected';
                    }
                    ?>
                >
                    Yearly
                </option>

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
                value="<?php echo htmlspecialchars($debt['interest_start_date']); ?>"
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
                value="<?php echo htmlspecialchars($debt['due_date']); ?>"
                required
            >

        </div>

        <br>

        <div>

            <label for="status">
                Status:
            </label>

            <select
                id="status"
                name="status"
                required
            >

                <option
                    value="active"
                    <?php
                    if ($debt['status'] === 'active') {
                        echo 'selected';
                    }
                    ?>
                >
                    Active
                </option>

                <option
                    value="paid"
                    <?php
                    if ($debt['status'] === 'paid') {
                        echo 'selected';
                    }
                    ?>
                >
                    Paid
                </option>

                <option
                    value="overdue"
                    <?php
                    if ($debt['status'] === 'overdue') {
                        echo 'selected';
                    }
                    ?>
                >
                    Overdue
                </option>

            </select>

        </div>

        <br>

        <button type="submit">
            Update Debt
        </button>

    </form>

</body>

</html>