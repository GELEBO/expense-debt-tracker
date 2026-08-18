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

$debtStmt = $pdo->prepare("
    SELECT
        id,
        creditor,
        original_amount
    FROM debts
    WHERE id = ?
");

$debtStmt->execute([$debt_id]);

$debt = $debtStmt->fetch(PDO::FETCH_ASSOC);

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
        id,
        payment_date,
        amount,
        note
    FROM debt_payments
    WHERE debt_id = ?
    ORDER BY payment_date DESC, id DESC
");

$paymentStmt->execute([$debt_id]);

$payments = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

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
        Original Debt:
        <strong>
            <?php
            echo number_format(
                $debt['original_amount'],
                2
            );
            ?>
            ETB
        </strong>
    </p>

    <?php if (count($payments) > 0): ?>

        <table border="1" cellpadding="10">

            <thead>

                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Note</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach ($payments as $payment): ?>

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
                            $payment['amount'],
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