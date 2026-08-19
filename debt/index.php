<?php

require_once "../config/database.php";
require_once "../includes/interest.php";
/*
|--------------------------------------------------------------------------
| Fetch Debts
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        debts.id,
        debts.creditor,
        debts.description,
        debts.original_amount,
        debts.interest_rate,
        debts.interest_period,
        debts.interest_start_date,
        debts.due_date,
        debts.status,

        COALESCE(
            (
                SELECT SUM(debt_payments.amount)
                FROM debt_payments
                WHERE debt_payments.debt_id = debts.id
            ),
            0
        ) AS total_paid

    FROM debts

    ORDER BY debts.due_date ASC
");

$debts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        <?php foreach ($debts as $debt): ?>

            <?php

                  $remaining =
                        max(
                             0,
                                   (float) $debt['original_amount']
                                  - (float) $debt['total_paid']
                            );

                  $elapsedDays = calculateElapsedDays(
                  $debt['interest_start_date']
                    );

                   $accruedInterest = calculateAccruedInterest(
                       $remaining,
                     (float) $debt['interest_rate'],
                      $debt['interest_period'],
                  $elapsedDays
                         );

                   $totalOwed = $remaining + $accruedInterest;
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
                        $debt['description']
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo number_format(
                        $debt['original_amount'],
                        2
                    );
                    ?>
                    ETB
                </td>

                <td>
                    <?php
                    echo number_format(
                        $debt['interest_rate'],
                        2
                    );
                    ?>%
                </td>

                <td>
                    <?php
                    echo number_format(
                        $debt['total_paid'],
                        2
                    );
                    ?>
                    ETB
                </td>

                <td>
                    <?php
                    echo number_format(
                        $remaining,
                        2
                    );
                    ?>
                    ETB
                </td>
                <td>
           <?php
                echo number_format(   $accruedInterest, 2 );
                ?>
                ETB
                  </td>

                <td>
           <?php
               echo number_format(  $totalOwed,  2 );
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
                        $debt['status']
                    );
                    ?>
                </td>

                <td>
                <a href="view.php?id=<?php echo $debt['id']; ?>"> View
                  </a>
|

                <a href="edit.php?id=<?php echo $debt['id']; ?>">
        Edit
    </a>
    |

    <a href="payment.php?debt_id=<?php echo $debt['id']; ?>">
        Record Payment
    </a>
    |

    <a href="payments.php?debt_id=<?php echo $debt['id']; ?>">
        Payment History
    </a>

    |

    <form
    method="POST"
    action="delete.php"
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

        </tbody>

    </table>
    </div>
</body>

</html>