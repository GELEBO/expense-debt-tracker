<?php

require_once "../config/database.php";

$id = $_GET['id'] ?? null;

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

header("Location: index.php");
exit;