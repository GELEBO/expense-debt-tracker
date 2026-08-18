<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Get Expense ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid expense ID.");
}

/*
|--------------------------------------------------------------------------
| Fetch Expense
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        expenses.id,
        expenses.expense_date,
        expenses.amount,
        expenses.description,
        expenses.category_id
    FROM expenses
    WHERE expenses.id = ?
");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    die("Expense not found.");
}

/*
|--------------------------------------------------------------------------
| Fetch Categories
|--------------------------------------------------------------------------
*/

$categoryStmt = $pdo->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");

$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Update Expense
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $expense_date = $_POST['expense_date'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';

    if (
        empty($expense_date) ||
        empty($category_id) ||
        empty($amount)
    ) {
        die("Please fill in all required fields.");
    }

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

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Expense | Expense & Debt Tracker</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <h1>Edit Expense</h1>

    <p>
        <a href="index.php">← Back to Expenses</a>
    </p>

    <form method="POST">

        <div>
            <label for="expense_date">Date:</label>
            <input
                type="date"
                id="expense_date"
                name="expense_date"
                value="<?php echo htmlspecialchars($expense['expense_date']); ?>"
                required
            >
        </div>

        <br>

        <div>
            <label for="category_id">Category:</label>

            <select
                id="category_id"
                name="category_id"
                required
            >

                <?php foreach ($categories as $category): ?>

                    <option
                        value="<?php echo $category['id']; ?>"
                        <?php
                        if ($category['id'] == $expense['category_id']) {
                            echo 'selected';
                        }
                        ?>
                    >
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>

        <br>

        <div>
            <label for="amount">Amount:</label>

            <input
                type="number"
                step="0.01"
                id="amount"
                name="amount"
                value="<?php echo htmlspecialchars($expense['amount']); ?>"
                required
            >
        </div>

        <br>

        <div>
            <label for="description">Description:</label>

            <textarea
                id="description"
                name="description"
                rows="4"
                cols="40"
            ><?php echo htmlspecialchars($expense['description']); ?></textarea>
        </div>

        <br>

        <button type="submit">Update Expense</button>

    </form>

</body>

</html>