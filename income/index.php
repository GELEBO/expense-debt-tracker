<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Income History Filter
|--------------------------------------------------------------------------
*/

$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

$sql = "
    SELECT
        id,
        income_date,
        source,
        amount,
        description
    FROM income
";

$params = [];

$conditions = [];

if ($fromDate !== '') {
    $conditions[] = "income_date >= :from_date";
    $params[':from_date'] = $fromDate;
}

if ($toDate !== '') {
    $conditions[] = "income_date <= :to_date";
    $params[':to_date'] = $toDate;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY income_date DESC, id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$incomes = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Calculate Total Income
|--------------------------------------------------------------------------
*/

$totalIncome = 0;

foreach ($incomes as $income) {
    $totalIncome += (float) $income['amount'];
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
    Total Income:
    <?php echo number_format($totalIncome, 2); ?>
    ETB
</h2>

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