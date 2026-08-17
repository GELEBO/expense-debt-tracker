<?php

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $userId = 1;
    $expenseDate = $_POST["expense_date"];
    $categoryId = $_POST["category_id"];
    $amount = $_POST["amount"];
    $description = $_POST["description"];

    $stmt = $pdo->prepare("
        INSERT INTO expenses
        (user_id, category_id, expense_date, amount, description)
        VALUES
        (:user_id, :category_id, :expense_date, :amount, :description)
    ");

    $stmt->execute([
        ":user_id" => $userId,
        ":category_id" => $categoryId,
        ":expense_date" => $expenseDate,
        ":amount" => $amount,
        ":description" => $description
    ]);

    header("Location: index.php");
    exit;
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense | Expense & Debt Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <h1>Add Expense</h1>

    <form method="POST">

        <label for="expense_date">Date</label>
        <input
            type="date"
            id="expense_date"
            name="expense_date"
            required
        >

        <br><br>

        <label for="category_id">Category</label>
        <select id="category_id" name="category_id" required>

            <option value="">Select category</option>

            <?php foreach ($categories as $category): ?>

                <option value="<?php echo $category['id']; ?>">
                    <?php echo htmlspecialchars($category['name']); ?>
                </option>

            <?php endforeach; ?>

        </select>

        <br><br>

        <label for="amount">Amount</label>
        <input
            type="number"
            id="amount"
            name="amount"
            step="0.01"
            min="0"
            required
        >

        <br><br>

        <label for="description">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
        ></textarea>

        <br><br>

        <button type="submit">Save Expense</button>

    </form>

    <p>
        <a href="index.php">← Back to Expenses</a>
    </p>

</body>
</html>