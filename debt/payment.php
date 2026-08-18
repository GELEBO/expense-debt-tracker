<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Get Debt ID
|--------------------------------------------------------------------------
*/

$debt_id = $_GET['debt_id'] ?? null;

if (!$debt_id || !is_numeric($debt_id)) {
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
        original_amount
    FROM debts
    WHERE id = ?
");

$stmt->execute([$debt_id]);

$debt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

/*
|--------------------------------------------------------------------------
| Calculate Total Paid
|--------------------------------------------------------------------------
*/

$paidStmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM debt_payments
    WHERE debt_id = ?
");

$paidStmt->execute([$debt_id]);

$total_paid = $paidStmt->fetchColumn();

$remaining = $debt['original_amount'] - $total_paid;

/*
|--------------------------------------------------------------------------
| Save Payment
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_date = $_POST['payment_date'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if (empty($payment_date) || empty($amount)) {
        die("Please fill in the payment date and amount.");
    }

    if (!is_numeric($amount) || $amount <= 0) {
        die("Please enter a valid payment amount.");
    }

    if ($amount > $remaining) {
        die("Payment cannot be greater than the remaining debt.");
    }

    $paymentStmt = $pdo->prepare("
        INSERT INTO debt_payments
        (
            debt_id,
            payment_date,
            amount,
            note
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?
        )
    ");

    $paymentStmt->execute([
        $debt_id,
        $payment_date,
        $amount,
        $note
    ]);

    /*
    |--------------------------------------------------------------------------
    | Mark Debt as Paid
    |--------------------------------------------------------------------------
    */

    $new_remaining = $remaining - $amount;

    if ($new_remaining == 0) {

        $statusStmt = $pdo->prepare("
            UPDATE debts
            SET status = 'paid'
            WHERE id = ?
        ");

        $statusStmt->execute([$debt_id]);
    }

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

    <title>Record Payment | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Record Debt Payment</h1>

    <p>
        <a href="index.php">
            ← Back to Debts
        </a>
    </p>

    <h2>
        <?php echo htmlspecialchars($debt['creditor']); ?>
    </h2>

    <p>
        Original Debt:
        <strong>
            <?php echo number_format($debt['original_amount'], 2); ?>
            ETB
        </strong>
    </p>

    <p>
        Already Paid:
        <strong>
            <?php echo number_format($total_paid, 2); ?>
            ETB
        </strong>
    </p>

    <p>
        Remaining:
        <strong>
            <?php echo number_format($remaining, 2); ?>
            ETB
        </strong>
    </p>

    <?php if ($remaining > 0): ?>

        <form method="POST">

            <div>

                <label for="payment_date">
                    Payment Date:
                </label>

                <input
                    type="date"
                    id="payment_date"
                    name="payment_date"
                    value="<?php echo date('Y-m-d'); ?>"
                    required
                >

            </div>

            <br>

            <div>

                <label for="amount">
                    Payment Amount:
                </label>

                <input
                    type="number"
                    id="amount"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    max="<?php echo htmlspecialchars($remaining); ?>"
                    required
                >

            </div>

            <br>

            <div>

                <label for="note">
                    Note:
                </label>

                <textarea
                    id="note"
                    name="note"
                    rows="4"
                    cols="40"
                ></textarea>

            </div>

            <br>

            <button type="submit">
                Record Payment
            </button>

        </form>

    <?php else: ?>

        <p>
            This debt has already been fully paid.
        </p>

    <?php endif; ?>

</body>

</html>