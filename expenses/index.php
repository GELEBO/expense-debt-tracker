<?php

require_once "../config/database.php";
$stmt = $pdo->query("
    SELECT
        expenses.id,
        expenses.expense_date,
        expenses.amount,
        expenses.description,
        categories.name AS category
    FROM expenses
    JOIN categories
        ON expenses.category_id = categories.id
    ORDER BY expenses.expense_date DESC
");

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses | Expense & Debt Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <h1>Expenses</h1>

    <p>
        <a href="../index.php">← Back to Dashboard</a>
    </p>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($expenses as $expense): ?>

            <tr>
                <td>
                    <?php echo htmlspecialchars($expense['expense_date']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars($expense['category']); ?>
                </td>

                <td>
                    <?php echo number_format($expense['amount'], 2); ?> ETB
                </td>

                <td>
                    <?php echo htmlspecialchars($expense['description']); ?>
                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

</body>
</html>