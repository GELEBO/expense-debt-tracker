<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../config/language.php";

/*
|--------------------------------------------------------------------------
| Get Expense ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die(__('invalid_expense_id'));
}


/*
|--------------------------------------------------------------------------
| Fetch Expense
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        expense_date,
        amount,
        description,
        category_id
    FROM expenses
    WHERE id = ?
");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    die(__('expense_not_found'));
}


/*
|--------------------------------------------------------------------------
| Update Expense
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $expense_date = $_POST['expense_date'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($expense_date) ||
        empty($category_id) ||
        empty($amount)
    ) {
        die(__('fill_required_fields'));
    }


    if (!is_numeric($amount) || $amount <= 0) {
        die(__('invalid_expense_amount'));
    }


    if (!is_numeric($category_id)) {
        die(__('invalid_expense_category'));
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Expense Category
    |--------------------------------------------------------------------------
    */

    $categoryStmt = $pdo->prepare("
        SELECT id
        FROM categories
        WHERE id = ?
        AND type = 'expense'
    ");

    $categoryStmt->execute([$category_id]);

    if (!$categoryStmt->fetch()) {
        die(__('invalid_expense_category'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update Expense
    |--------------------------------------------------------------------------
    */

    $updateStmt = $pdo->prepare("
        UPDATE expenses
        SET
            expense_date = ?,
            category_id = ?,
            amount = ?,
            description = ?
        WHERE id = ?
    ");

    $updateStmt->execute([
        $expense_date,
        $category_id,
        $amount,
        $description,
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Return to Expense List
    |--------------------------------------------------------------------------
    */

    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Fetch Expense Categories
|--------------------------------------------------------------------------
*/

$categoryStmt = $pdo->query("
    SELECT
        id,
        name
    FROM categories
    WHERE type = 'expense'
    ORDER BY name ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Category Translation
|--------------------------------------------------------------------------
*/

$categoryTranslations = [

    'Food' => __('category_food'),

    'Transport' => __('category_transport'),

    'Health' => __('category_health'),

    'Medicine' => __('category_medicine'),

    'Education' => __('category_education'),

    'Housing' => __('category_housing'),

    'Utilities' => __('category_utilities'),

    'Communication' => __('category_communication'),

    'Clothing' => __('category_clothing'),

    'Family' => __('category_family'),

    'Personal' => __('category_personal'),

    'Help' => __('category_help'),

    'Treatment' => __('category_treatment'),

    'Tithe' => __('category_tithe'),

    'Other' => __('category_other')

];

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
        <?= __('edit_expense') ?> |
        <?= __('app_name') ?>
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

        <?= __('edit_expense') ?>

    </h1>


    <!-- =========================
         BACK TO EXPENSES
         ========================= -->

    <p>

        <a href="index.php">

            ← <?= __('back_to_expenses') ?>

        </a>

    </p>


    <!-- =========================
         EDIT FORM
         ========================= -->

    <form method="POST">


        <!-- Date -->

        <div>

            <label for="expense_date">

                <?= __('date') ?>:

            </label>

            <input
                type="date"
                id="expense_date"
                name="expense_date"
                value="<?= htmlspecialchars($expense['expense_date']) ?>"
                required
            >

        </div>


        <br>


        <!-- Category -->

        <div>

            <label for="category_id">

                <?= __('category') ?>:

            </label>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <option value="">

                    <?= __('select_category') ?>

                </option>


                <?php foreach ($categories as $category): ?>

                    <?php

                    $categoryName = $category['name'];

                    $displayCategory =
                        $categoryTranslations[$categoryName]
                        ?? $categoryName;

                    ?>

                    <option
                        value="<?= $category['id'] ?>"
                        <?= (
                            $category['id']
                            == $expense['category_id']
                        ) ? 'selected' : '' ?>
                    >

                        <?= htmlspecialchars($displayCategory) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <br>


        <!-- Amount -->

        <div>

            <label for="amount">

                <?= __('amount') ?>:

            </label>

            <input
                type="number"
                id="amount"
                name="amount"
                step="0.01"
                min="0.01"
                value="<?= htmlspecialchars($expense['amount']) ?>"
                required
            >

        </div>


        <br>


        <!-- Description -->

        <div>

            <label for="description">

                <?= __('description') ?>:

            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
            ><?= htmlspecialchars($expense['description']) ?></textarea>

        </div>


        <br>


        <!-- Update -->

        <button type="submit">

            <?= __('update_expense') ?>

        </button>


    </form>


</body>

</html>