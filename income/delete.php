<?php

require_once "../config/database.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("Invalid income ID.");
}

/*
|--------------------------------------------------------------------------
| Check if Income Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
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
| Delete Income
|--------------------------------------------------------------------------
*/

$deleteStmt = $pdo->prepare("
    DELETE FROM income
    WHERE id = ?
");

$deleteStmt->execute([$id]);

header("Location: index.php");
exit;