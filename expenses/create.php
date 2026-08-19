<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Create Expense
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = 1;
    $expenseDate = $_POST['expense_date'] ?? '';
    $categoryId = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = trim($_POST['description'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($expenseDate) ||
        empty($categoryId) ||
        empty($amount)
    ) {
        die("Please fill in all required fields.");
    }

    if (!is_numeric($amount) || $amount <= 0) {
        die("Please enter a valid expense amount.");
    }

    if (!is_numeric($categoryId)) {
        die("Please select a valid expense category.");
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

    $categoryStmt->execute([$categoryId]);

    if (!$categoryStmt->fetch()) {
        die("Invalid expense category.");
    }

    /*
    |--------------------------------------------------------------------------
    | Insert Expense
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO expenses
        (
            user_id,
            category_id,
            expense_date,
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
        $userId,
        $categoryId,
        $expenseDate,
        $amount,
        $description
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

$stmt = $pdo->query("
    SELECT id, name
    FROM categories
    WHERE type = 'expense'
    ORDER BY name
");

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Expense | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Add Expense</h1>

    <p>
        <a href="../index.php">
            ← Back to Dashboard
        </a>
    </p>

    <form method="POST">

        <div>

            <label for="expense_date">
                Date:
            </label>

            <input
                type="date"
                id="expense_date"
                name="expense_date"
                value="<?php echo date('Y-m-d'); ?>"
                required
            >

        </div>

        <br>

        <div>

            <label for="category_id">
                Category:
            </label>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <option value="">
                    Select category
                </option>

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?php echo $category['id']; ?>"
                    >
                        <?php
                        echo htmlspecialchars(
                            $category['name']
                        );
                        ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>

            <label for="amount">
                Amount:
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

        <div>

            <label for="description">
                Description:
            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
            ></textarea>

        </div>

        <br>

        <button type="submit">
            Save Expense
        </button>

    </form>

    <p>
        <a href="index.php">
            ← Back to Expenses
        </a>
    </p>

</body>

</html>