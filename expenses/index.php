<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../config/language.php";

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


/*
|--------------------------------------------------------------------------
| User Filter
|--------------------------------------------------------------------------
*/

$conditions[] = "expenses.user_id = :user_id";

$params[':user_id'] = $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

if ($fromDate !== '') {

    $conditions[] = "expenses.expense_date >= :from_date";

    $params[':from_date'] = $fromDate;
}

if ($toDate !== '') {

    $conditions[] = "expenses.expense_date <= :to_date";

    $params[':to_date'] = $toDate;
}


/*
|--------------------------------------------------------------------------
| Build WHERE Clause
|--------------------------------------------------------------------------
*/

if (!empty($conditions)) {

    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY expenses.expense_date DESC, expenses.id DESC";


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

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

<html lang="<?= htmlspecialchars($_SESSION['language'] ?? 'en') ?>">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= __('expenses') ?> | <?= __('app_name') ?>
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>


    <!-- =========================
         PAGE TITLE
         ========================= -->

    <h1>
        <?= __('expenses') ?>
    </h1>


    <!-- =========================
         BACK TO DASHBOARD
         ========================= -->

    <p>

        <a href="../index.php">

            ← <?= __('back_to_dashboard') ?>

        </a>

    </p>


    <!-- =========================
         ADD EXPENSE
         ========================= -->

    <p>

        <a href="create.php">

            + <?= __('add_expense') ?>

        </a>

    </p>


    <!-- =========================
         DATE FILTER
         ========================= -->

    <form method="GET">

        <label for="from_date">

            <?= __('from') ?>:

        </label>


        <input
            type="date"
            id="from_date"
            name="from_date"
            value="<?= htmlspecialchars($fromDate) ?>"
        >


        <label for="to_date">

            <?= __('to') ?>:

        </label>


        <input
            type="date"
            id="to_date"
            name="to_date"
            value="<?= htmlspecialchars($toDate) ?>"
        >


        <button type="submit">

            <?= __('filter') ?>

        </button>


        <a href="index.php">

            <?= __('clear') ?>

        </a>

    </form>


    <!-- =========================
         TOTAL EXPENSES
         ========================= -->

    <h2>

        <?= __('total_expenses') ?>:

        <?= number_format($totalExpenses, 2) ?>

        ETB

    </h2>


    <!-- =========================
         EXPENSE HISTORY TABLE
         ========================= -->

    <table border="1" cellpadding="10">

        <thead>

            <tr>

                <th>
                    <?= __('date') ?>
                </th>

                <th>
                    <?= __('category') ?>
                </th>

                <th>
                    <?= __('amount') ?>
                </th>

                <th>
                    <?= __('description') ?>
                </th>

                <th>
                    <?= __('actions') ?>
                </th>

            </tr>

        </thead>


        <tbody>

        <?php foreach ($expenses as $expense): ?>

            <tr>


                <!-- Date -->

                <td>

                    <?= htmlspecialchars(
                        $expense['expense_date']
                    ) ?>

                </td>


                <!-- Category -->

                <td>

                    <?= htmlspecialchars(
                        $expense['category']
                    ) ?>

                </td>


                <!-- Amount -->

                <td>

                    <?= number_format(
                        $expense['amount'],
                        2
                    ) ?>

                    ETB

                </td>


                <!-- Description -->

                <td>

                    <?= htmlspecialchars(
                        $expense['description']
                    ) ?>

                </td>


                <!-- Actions -->

                <td>

                    <a
                        href="edit.php?id=<?= $expense['id'] ?>"
                    >
                        <?= __('edit') ?>
                    </a>


                    |


                    <form
                        method="POST"
                        action="delete.php"
                        style="display:inline;"
                        onsubmit="return confirm('<?= htmlspecialchars(__('delete_expense_confirmation'), ENT_QUOTES) ?>');"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $expense['id'] ?>"
                        >


                        <button type="submit">

                            <?= __('delete') ?>

                        </button>

                    </form>

                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>


</body>

</html>