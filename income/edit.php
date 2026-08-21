
<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../config/language.php";

/*
|--------------------------------------------------------------------------
| Get Income ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die(__('invalid_income_id'));
}


/*
|--------------------------------------------------------------------------
| Fetch Income
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        income_date,
        source,
        amount,
        description
    FROM income
    WHERE id = ?
");

$stmt->execute([$id]);

$income = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$income) {
    die(__('income_not_found'));
}


/*
|--------------------------------------------------------------------------
| Update Income
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $income_date = $_POST['income_date'] ?? '';
    $source = trim($_POST['source'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Required Fields
    |--------------------------------------------------------------------------
    */

    if (
        empty($income_date) ||
        empty($source) ||
        empty($amount)
    ) {
        die(__('fill_required_fields'));
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Amount
    |--------------------------------------------------------------------------
    */

    if (!is_numeric($amount) || $amount <= 0) {
        die(__('invalid_income_amount'));
    }


    /*
    |--------------------------------------------------------------------------
    | Update Database
    |--------------------------------------------------------------------------
    */

    $updateStmt = $pdo->prepare("
        UPDATE income
        SET
            income_date = ?,
            source = ?,
            amount = ?,
            description = ?
        WHERE id = ?
    ");

    $updateStmt->execute([
        $income_date,
        $source,
        $amount,
        $description,
        $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Return to Income History
    |--------------------------------------------------------------------------
    */

    header("Location: index.php");
    exit;
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
        <?= __('edit_income') ?> | <?= __('app_name') ?>
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
        <?= __('edit_income') ?>
    </h1>


    <!-- =========================
         BACK TO INCOME
         ========================= -->

    <p>

        <a href="index.php">

            ← <?= __('back_to_income') ?>

        </a>

    </p>


    <!-- =========================
         EDIT FORM
         ========================= -->

    <form method="POST">


        <!-- Date -->

        <div>

            <label for="income_date">

                <?= __('date') ?>:

            </label>


            <input
                type="date"
                id="income_date"
                name="income_date"
                value="<?= htmlspecialchars($income['income_date']) ?>"
                required
            >

        </div>


        <br>


        <!-- Source -->

        <div>

            <label for="source">

                <?= __('source') ?>:

            </label>


            <input
                type="text"
                id="source"
                name="source"
                value="<?= htmlspecialchars($income['source']) ?>"
                required
            >

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
                value="<?= htmlspecialchars($income['amount']) ?>"
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
                cols="40"
            ><?= htmlspecialchars($income['description']) ?></textarea>

        </div>


        <br>


        <!-- Update -->

        <button type="submit">

            <?= __('update_income') ?>

        </button>


    </form>


</body>

</html>