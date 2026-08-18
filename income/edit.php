<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Get Income ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid income ID.");
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
    die("Income record not found.");
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

    if (
        empty($income_date) ||
        empty($source) ||
        empty($amount)
    ) {
        die("Please fill in all required fields.");
    }

    if (!is_numeric($amount) || $amount <= 0) {
        die("Please enter a valid income amount.");
    }

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

    header("Location: index.php");
    exit;
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

    <title>Edit Income | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Edit Income</h1>

    <p>
        <a href="index.php">
            ← Back to Income
        </a>
    </p>

    <form method="POST">

        <div>

            <label for="income_date">
                Date:
            </label>

            <input
                type="date"
                id="income_date"
                name="income_date"
                value="<?php echo htmlspecialchars($income['income_date']); ?>"
                required
            >

        </div>

        <br>

        <div>

            <label for="source">
                Source:
            </label>

            <input
                type="text"
                id="source"
                name="source"
                value="<?php echo htmlspecialchars($income['source']); ?>"
                required
            >

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
                value="<?php echo htmlspecialchars($income['amount']); ?>"
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
                cols="40"
            ><?php echo htmlspecialchars($income['description']); ?></textarea>

        </div>

        <br>

        <button type="submit">
            Update Income
        </button>

    </form>

</body>

</html>