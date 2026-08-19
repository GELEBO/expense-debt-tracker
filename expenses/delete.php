<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Delete Expense
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid expense ID.");
}

/*
|--------------------------------------------------------------------------
| Check if Expense Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM expenses
    WHERE id = ?
");

$stmt->execute([$id]);

$expense = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$expense) {
    die("Expense not found.");
}

/*
|--------------------------------------------------------------------------
| Delete Expense
|--------------------------------------------------------------------------
*/

$deleteStmt = $pdo->prepare("
    DELETE FROM expenses
    WHERE id = ?
");

$deleteStmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Return to Expenses
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;