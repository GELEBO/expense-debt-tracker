<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/interest.php";

$userId = $_SESSION['user_id'];

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
        original_amount,
        interest_rate,
        interest_period,
        interest_start_date,
        status
    FROM debts
    WHERE id = ?
    AND user_id = ?
");

$stmt->execute([
    $debt_id,
    $userId
]);

$debt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

/*
|--------------------------------------------------------------------------
| Fetch Payment History
|--------------------------------------------------------------------------
*/

$paymentsStmt = $pdo->prepare("
    SELECT
        payment_date,
        amount,
        note
    FROM debt_payments
    WHERE debt_id = ?
    ORDER BY payment_date ASC, id ASC
");

$paymentsStmt->execute([
    $debt_id
]);

$payments = $paymentsStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Calculate Current Debt Balance
|--------------------------------------------------------------------------
*/

$balance = calculateDebtBalance(
    (float) $debt['original_amount'],
    (float) $debt['interest_rate'],
    $debt['interest_period'],
    $debt['interest_start_date'],
    $payments
);

/*
|--------------------------------------------------------------------------
| Save Payment
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $payment_date = $_POST['payment_date'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $note = trim($_POST['note'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validate Payment Date
    |--------------------------------------------------------------------------
    */

    if (empty($payment_date)) {
        die("Please select a payment date.");
    }

    $paymentDateObject = DateTime::createFromFormat(
        'Y-m-d',
        $payment_date
    );

    $today = new DateTime();

    if (
        !$paymentDateObject ||
        $paymentDateObject->format('Y-m-d') !== $payment_date
    ) {
        die("Invalid payment date.");
    }

    /*
    |--------------------------------------------------------------------------
    | Payment cannot be in the future
    |--------------------------------------------------------------------------
    */

    if ($paymentDateObject > $today) {
        die("Payment date cannot be in the future.");
    }

    /*
    |--------------------------------------------------------------------------
    | Payment cannot be before interest start date
    |--------------------------------------------------------------------------
    */

    $interestStartObject = new DateTime(
        $debt['interest_start_date']
    );

    if ($paymentDateObject < $interestStartObject) {
        die("Payment date cannot be before the interest start date.");
    }

    /*
    |--------------------------------------------------------------------------
    | Validate Amount
    |--------------------------------------------------------------------------
    */

    if ($amount === '' || !is_numeric($amount)) {
        die("Please enter a valid payment amount.");
    }

    $amount = (float) $amount;

    if ($amount <= 0) {
        die("Payment amount must be greater than zero.");
    }

    /*
    |--------------------------------------------------------------------------
    | Calculate Balance on the Payment Date
    |--------------------------------------------------------------------------
    |
    | This is important.
    |
    | If someone records a payment today, interest must first be
    | calculated up to today.
    |
    | If someone records a historical payment, we calculate the
    | debt balance as it existed on that payment date.
    |
    */

    $balanceAtPaymentDate = calculateDebtBalance(
        (float) $debt['original_amount'],
        (float) $debt['interest_rate'],
        $debt['interest_period'],
        $debt['interest_start_date'],
        $payments,
        $payment_date
    );

    /*
    |--------------------------------------------------------------------------
    | Check Remaining Debt
    |--------------------------------------------------------------------------
    */

    $remainingAtPaymentDate =
        $balanceAtPaymentDate['total_owed'];

    if ($amount > ($remainingAtPaymentDate + 0.00001)) {
        die(
            "Payment cannot be greater than the total amount owed " .
            "on the selected payment date."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Save Payment
    |--------------------------------------------------------------------------
    */

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
    | Fetch Updated Payment History
    |--------------------------------------------------------------------------
    */

    $updatedPaymentsStmt = $pdo->prepare("
        SELECT
            payment_date,
            amount,
            note
        FROM debt_payments
        WHERE debt_id = ?
        ORDER BY payment_date ASC, id ASC
    ");

    $updatedPaymentsStmt->execute([
        $debt_id
    ]);

    $updatedPayments =
        $updatedPaymentsStmt->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Recalculate Complete Debt
    |--------------------------------------------------------------------------
    */

    $updatedBalance = calculateDebtBalance(
        (float) $debt['original_amount'],
        (float) $debt['interest_rate'],
        $debt['interest_period'],
        $debt['interest_start_date'],
        $updatedPayments
    );

    /*
    |--------------------------------------------------------------------------
    | Update Debt Status
    |--------------------------------------------------------------------------
    */

    $newStatus = $updatedBalance['status'];

    $statusStmt = $pdo->prepare("
        UPDATE debts
        SET status = ?
        WHERE id = ?
        AND user_id = ?
    ");

    $statusStmt->execute([
        $newStatus,
        $debt_id,
        $userId
    ]);

    /*
    |--------------------------------------------------------------------------
    | Redirect
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
        Original Principal:
        <strong>
            <?php
            echo number_format(
                $balance['original_principal'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <p>
        Principal Remaining:
        <strong>
            <?php
            echo number_format(
                $balance['principal'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <p>
        Accrued Interest:
        <strong>
            <?php
            echo number_format(
                $balance['accrued_interest'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <p>
        Total Owed:
        <strong>
            <?php
            echo number_format(
                $balance['total_owed'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <p>
        Total Paid:
        <strong>
            <?php
            echo number_format(
                $balance['total_paid'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <?php if ($balance['total_owed'] > 0.00001): ?>

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
                    min="<?php echo htmlspecialchars($debt['interest_start_date']); ?>"
                    max="<?php echo date('Y-m-d'); ?>"
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