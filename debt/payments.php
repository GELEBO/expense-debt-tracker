<?php

require_once "../config/database.php";
require_once "../includes/interest.php";

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

$debtStmt = $pdo->prepare("
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
");

$debtStmt->execute([
    $debt_id
]);

$debt = $debtStmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

/*
|--------------------------------------------------------------------------
| Fetch Payment History
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Payments are retrieved chronologically because the calculation
| engine processes them in chronological order.
|
*/

$paymentStmt = $pdo->prepare("
    SELECT
        id,
        payment_date,
        amount,
        note
    FROM debt_payments
    WHERE debt_id = ?
    ORDER BY payment_date ASC, id ASC
");

$paymentStmt->execute([
    $debt_id
]);

$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Calculate Debt
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
| Calculated Payment History
|--------------------------------------------------------------------------
*/

$paymentHistory = $balance['payment_history'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment History | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Payment History</h1>

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
        Interest Rate:
        <strong>
            <?php
            echo number_format(
                (float) $debt['interest_rate'],
                2
            );
            ?>%
            <?php echo htmlspecialchars($debt['interest_period']); ?>
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

    <p>
        Status:
        <strong>
            <?php
            echo htmlspecialchars(
                $balance['status']
            );
            ?>
        </strong>
    </p>

    <hr>

    <h3>Payment Records</h3>

    <?php if (count($paymentHistory) > 0): ?>

        <table border="1" cellpadding="10">

            <thead>

                <tr>

                    <th>Date</th>

                    <th>Payment</th>

                    <th>Days</th>

                    <th>Interest Accrued</th>

                    <th>Interest Paid</th>

                    <th>Principal Paid</th>

                    <th>Remaining Principal</th>

                    <th>Remaining Interest</th>

                    <th>Note</th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($paymentHistory as $payment): ?>

                <tr>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment['payment_date']
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['payment_amount'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo $payment['elapsed_days'];
                        ?>
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['interest_accrued'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['interest_paid'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['principal_paid'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['remaining_principal'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $payment['remaining_interest'],
                            2
                        );
                        ?>
                        ETB
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $payment['note']
                        );
                        ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    <?php else: ?>

        <p>
            No payments have been recorded for this debt yet.
        </p>

    <?php endif; ?>

</body>

</html>