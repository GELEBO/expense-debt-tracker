<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Fetch Income
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        income_date,
        source,
        amount,
        description
    FROM income
    ORDER BY income_date DESC, id DESC
");

$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Income | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Income</h1>

    <p>
        <a href="../index.php">
            ← Back to Dashboard
        </a>
    </p>

    <p>
        <a href="create.php">
            + Add Income
        </a>
    </p>

    <table border="1" cellpadding="10">

        <thead>

            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Action</th>
            </tr>

        </thead>

        <tbody>

        <?php foreach ($incomes as $income): ?>

            <tr>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $income['income_date']
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $income['source']
                    );
                    ?>
                </td>

                <td>
                    <?php
                    echo number_format(
                        $income['amount'],
                        2
                    );
                    ?>
                    ETB
                </td>

                <td>
                    <?php
                    echo htmlspecialchars(
                        $income['description']
                    );
                    ?>
                </td>

                <td>

                <a href="edit.php?id=<?php echo $income['id']; ?>">
                 Edit
                 </a>

                    |

                    <a
                       href="delete.php?id=<?php echo $income['id']; ?>"
                       onclick="return confirm('Are you sure you want to delete this income record?');"
                         >
                      Delete
                    </a>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</body>

</html>