
<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../config/language.php";

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


/*
|--------------------------------------------------------------------------
| User Filter
|--------------------------------------------------------------------------
*/

$conditions[] = "user_id = :user_id";

$params[':user_id'] = $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

if ($fromDate !== '') {

    $conditions[] = "income_date >= :from_date";

    $params[':from_date'] = $fromDate;
}

if ($toDate !== '') {

    $conditions[] = "income_date <= :to_date";

    $params[':to_date'] = $toDate;
}


/*
|--------------------------------------------------------------------------
| Build WHERE Clause
|--------------------------------------------------------------------------
*/

$sql .= " WHERE " . implode(" AND ", $conditions);

$sql .= " ORDER BY income_date DESC, id DESC";


/*
|--------------------------------------------------------------------------
| Execute Query
|--------------------------------------------------------------------------
*/

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
<html lang="<?= htmlspecialchars($_SESSION['language'] ?? 'en') ?>">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= __('income') ?> | <?= __('app_name') ?>
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
        <?= __('income') ?>
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
         ADD INCOME
         ========================= -->

    <p>

        <a href="create.php">

            + <?= __('add_income') ?>

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
         TOTAL INCOME
         ========================= -->

    <h2>

        <?= __('total_income') ?>:

        <?= number_format($totalIncome, 2) ?>

        ETB

    </h2>


    <!-- =========================
         INCOME HISTORY TABLE
         ========================= -->

    <table border="1" cellpadding="10">

        <thead>

            <tr>

                <th>
                    <?= __('date') ?>
                </th>

                <th>
                    <?= __('source') ?>
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

        <?php foreach ($incomes as $income): ?>

            <tr>


                <!-- Date -->

                <td>

                    <?= htmlspecialchars(
                        $income['income_date']
                    ) ?>

                </td>


                <!-- Source -->

                <td>

                    <?= htmlspecialchars(
                        $income['source']
                    ) ?>

                </td>


                <!-- Amount -->

                <td>

                    <?= number_format(
                        $income['amount'],
                        2
                    ) ?>

                    ETB

                </td>


                <!-- Description -->

                <td>

                    <?= htmlspecialchars(
                        $income['description']
                    ) ?>

                </td>


                <!-- Actions -->

                <td>

                    <a
                        href="edit.php?id=<?= $income['id'] ?>"
                    >
                        <?= __('edit') ?>
                    </a>


                    |


                    <form
                        method="POST"
                        action="delete.php"
                        style="display:inline;"
                        onsubmit="return confirm('<?= htmlspecialchars(__('delete_income_confirmation'), ENT_QUOTES) ?>');"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= $income['id'] ?>"
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