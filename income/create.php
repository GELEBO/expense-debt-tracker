<?php

require_once "../includes/auth.php";
require_once "../config/database.php";
require_once "../config/language.php";

/*
|--------------------------------------------------------------------------
| Create Income
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $income_date = $_POST['income_date'] ?? '';
    $source = trim($_POST['source'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($income_date) ||
        empty($source) ||
        empty($amount)
    ) {
        die(__('fill_required_fields'));
    }


    if (!is_numeric($amount) || $amount <= 0) {
        die(__('invalid_income_amount'));
    }


    /*
    |--------------------------------------------------------------------------
    | Insert Income
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO income
        (
            user_id,
            income_date,
            source,
            amount,
            description
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([

        $_SESSION['user_id'],
        $income_date,
        $source,
        $amount,
        $description

    ]);


    /*
    |--------------------------------------------------------------------------
    | Return to Income List
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
        <?= __('add_income') ?> | <?= __('app_name') ?>
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

        <?= __('add_income') ?>

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
         ADD INCOME FORM
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
                value="<?= date('Y-m-d') ?>"
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
                placeholder="<?= htmlspecialchars(__('income_source_placeholder')) ?>"
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
            ></textarea>

        </div>


        <br>


        <!-- Save -->

        <button type="submit">

            <?= __('save_income') ?>

        </button>


    </form>


</body>

</html>