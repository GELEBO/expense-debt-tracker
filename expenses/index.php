<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Expense History Filter
|--------------------------------------------------------------------------
*/

$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$sql = "
    SELECT
        expenses.id,
        expenses.expense_date,
        expenses.amount,
        expenses.description,
        categories.name AS category
    FROM expenses
    JOIN categories
        ON expenses.category_id = categories.id
";

$params = [];
$conditions = [];

if ($fromDate !== '') {
    $conditions[] = "expenses.expense_date >= :from_date";
    $params[':from_date'] = $fromDate;
}

if ($toDate !== '') {
    $conditions[] = "expenses.expense_date <= :to_date";
    $params[':to_date'] = $toDate;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY expenses.expense_date DESC, expenses.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Calculate Total Expenses
|--------------------------------------------------------------------------
*/

$totalExpenses = 0;

foreach ($expenses as $expense) {
    $totalExpenses += (float) $expense['amount'];
}

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
    <p>
    <a href="create.php">+ Add Expense</a>
    </p>
    <form method="GET">

    <label for="from_date">
        From:
    </label>

    <input
        type="date"
        id="from_date"
        name="from_date"
        value="<?php echo htmlspecialchars($fromDate); ?>"
    >

    <label for="to_date">
        To:
    </label>

    <input
        type="date"
        id="to_date"
        name="to_date"
        value="<?php echo htmlspecialchars($toDate); ?>"
    >

    <button type="submit">
        Filter
    </button>

    <a href="index.php">
        Clear
    </a>

</form>

<h2>
    Total Expenses:
    <?php echo number_format($totalExpenses, 2); ?>
    ETB
</h2>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Action</th>
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

    <td>

        <a href="edit.php?id=<?php echo $expense['id']; ?>">
            Edit
        </a>

        |

        <a
            href="delete.php?id=<?php echo $expense['id']; ?>"
            onclick="return confirm('Are you sure you want to delete this expense?');"
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