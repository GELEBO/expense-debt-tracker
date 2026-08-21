<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../includes/interest.php";
require_once "../config/language.php";

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Fetch User's Debts
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
    WHERE user_id = :user_id
    ORDER BY due_date ASC
");

$stmt->execute([
    ':user_id' => $userId
]);

$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Fetch All Payment History
|--------------------------------------------------------------------------
|
| We retrieve payments for all of this user's debts at once.
| The calculation engine will process each debt's payments
| chronologically.
|
*/

$paymentStmt = $pdo->prepare("
    SELECT
        debt_payments.debt_id,
        debt_payments.payment_date,
        debt_payments.amount,
        debt_payments.note
    FROM debt_payments
    INNER JOIN debts
        ON debt_payments.debt_id = debts.id
    WHERE debts.user_id = :user_id
    ORDER BY
        debt_payments.debt_id ASC,
        debt_payments.payment_date ASC,
        debt_payments.id ASC
");

$paymentStmt->execute([
    ':user_id' => $userId
]);

$paymentRows = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Organize Payments By Debt
|--------------------------------------------------------------------------
*/

$paymentsByDebt = [];

foreach ($paymentRows as $payment) {

    $debtId = $payment['debt_id'];

    if (!isset($paymentsByDebt[$debtId])) {
        $paymentsByDebt[$debtId] = [];
    }

    $paymentsByDebt[$debtId][] = [
        'payment_date' => $payment['payment_date'],
        'amount' => $payment['amount'],
        'note' => $payment['note']
    ];
}

/*
|--------------------------------------------------------------------------
| Calculate Each Debt
|--------------------------------------------------------------------------
*/

foreach ($debts as &$debt) {

    $debtId = $debt['id'];

    $payments = $paymentsByDebt[$debtId] ?? [];

    $balance = calculateDebtBalance(
        (float) $debt['original_amount'],
        (float) $debt['interest_rate'],
        $debt['interest_period'],
        $debt['interest_start_date'],
        $payments
    );

    $debt['calculation'] = $balance;
}

unset($debt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Debts | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Debts</h1>

    <p>
        <a href="../index.php">
            ← Back to Dashboard
        </a>
    </p>

    <p>
        <a href="create.php">
            + Add Debt
        </a>
    </p>

    <div class="table-container">

        <table border="1" cellpadding="10">

            <thead>

                <tr>

                    <th>Creditor</th>

                    <th>Description</th>

                    <th>Original Amount</th>

                    <th>Interest Rate</th>

                    <th>Total Paid</th>

                    <th>Remaining Principal</th>

                    <th>Accrued Interest</th>

                    <th>Total Owed</th>

                    <th>Due Date</th>

                    <th>Status</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if (count($debts) > 0): ?>

                <?php foreach ($debts as $debt): ?>

                    <?php
                    $balance = $debt['calculation'];
                    ?>

                    <tr>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $debt['creditor']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $debt['description'] ?? ''
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $balance['original_principal'],
                                2
                            );
                            ?>
                            ETB
                        </td>

                        <td>
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
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $balance['total_paid'],
                                2
                            );
                            ?>
                            ETB
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $balance['principal'],
                                2
                            );
                            ?>
                            ETB
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $balance['accrued_interest'],
                                2
                            );
                            ?>
                            ETB
                        </td>

                        <td>
                            <?php
                            echo number_format(
                                $balance['total_owed'],
                                2
                            );
                            ?>
                            ETB
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $debt['due_date']
                            );
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $balance['status']
                            );
                            ?>
                        </td>

                        <td>

                            <a
                                href="view.php?id=<?php echo $debt['id']; ?>"
                            >
                                View
                            </a>

                            |

                            <a
                                href="edit.php?id=<?php echo $debt['id']; ?>"
                            >
                                Edit
                            </a>

                            |

                            <a
                                href="payment.php?debt_id=<?php echo $debt['id']; ?>"
                            >
                                Record Payment
                            </a>

                            |

                            <a
                                href="payments.php?debt_id=<?php echo $debt['id']; ?>"
                            >
                                Payment History
                            </a>

                            |

                            <form
                                method="POST"
                                action="delete.php"
                                style="display:inline;"
                                onsubmit="return confirm('Are you sure you want to delete this debt?');"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?php echo $debt['id']; ?>"
                                >

                                <button type="submit">
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="11">
                        No debts have been recorded yet.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</body>

</html>