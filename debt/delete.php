<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Delete Debt
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$id = $_POST['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid debt ID.");
}

/*
|--------------------------------------------------------------------------
| Check if Debt Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM debts
    WHERE id = ?
");

$stmt->execute([$id]);

$debt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

/*
|--------------------------------------------------------------------------
| Delete Debt
|--------------------------------------------------------------------------
*/

$deleteStmt = $pdo->prepare("
    DELETE FROM debts
    WHERE id = ?
");

$deleteStmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Return to Debts
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;