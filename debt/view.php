<?php

require_once "../config/database.php";
require_once "../includes/interest.php";

/*
|--------------------------------------------------------------------------
| Get Debt ID
|--------------------------------------------------------------------------
*/

$debtId = $_GET['id'] ?? null;

if (!$debtId || !is_numeric($debtId)) {
    die("Debt ID is required.");
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
    WHERE id = :id
");

$stmt->execute([
    ":id" => $debtId
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

$paymentStmt = $pdo->prepare("
    SELECT
        payment_date,
        amount,
        note
    FROM debt_payments
    WHERE debt_id = :debt_id
    ORDER BY payment_date ASC, id ASC
");

$paymentStmt->execute([
    ":debt_id" => $debtId
]);

$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

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
| Calculate Elapsed Days
|--------------------------------------------------------------------------
*/

$elapsedDays = calculateElapsedDays(
    $debt['interest_start_date']
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Debt Details</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Debt Details</h1>

    <p>
        <a href="index.php">
            ← Back to Debts
        </a>
    </p>

    <h2>
        <?php echo htmlspecialchars($debt['creditor']); ?>
    </h2>

    <p>
        <?php
        echo htmlspecialchars(
            $debt['description'] ?? ''
        );
        ?>
    </p>

    <hr>

    <p>
        <strong>Original Principal:</strong>
        <?php
        echo number_format(
            $balance['original_principal'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Total Paid:</strong>
        <?php
        echo number_format(
            $balance['total_paid'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Remaining Principal:</strong>
        <?php
        echo number_format(
            $balance['principal'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Interest Rate:</strong>
        <?php
        echo number_format(
            (float) $debt['interest_rate'],
            2
        );
        ?>%

        <?php
        echo htmlspecialchars(
            $debt['interest_period']
        );
        ?>
    </p>

    <p>
        <strong>Interest Start Date:</strong>
        <?php
        echo htmlspecialchars(
            $debt['interest_start_date']
        );
        ?>
    </p>

    <p>
        <strong>Elapsed Days:</strong>
        <?php
        echo $elapsedDays;
        ?>
        days
    </p>

    <p>
        <strong>Accrued Interest:</strong>
        <?php
        echo number_format(
            $balance['accrued_interest'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Total Amount Owed:</strong>
        <?php
        echo number_format(
            $balance['total_owed'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Total Interest Accrued:</strong>
        <?php
        echo number_format(
            $balance['total_interest_accrued'],
            2
        );
        ?>
        ETB
    </p>

    <p>
        <strong>Due Date:</strong>
        <?php
        echo htmlspecialchars(
            $debt['due_date']
        );
        ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?php
        echo htmlspecialchars(
            $balance['status']
        );
        ?>
    </p>

    <hr>

    <p>
        <a
            href="payments.php?debt_id=<?php echo $debtId; ?>"
        >
            View Payment History
        </a>
    </p>

    <?php if ($balance['total_owed'] > 0.00001): ?>

        <p>
            <a
                href="payment.php?debt_id=<?php echo $debtId; ?>"
            >
                Record Payment
            </a>
        </p>

    <?php else: ?>

        <p>
            This debt has been fully paid.
        </p>

    <?php endif; ?>

</body>

</html>