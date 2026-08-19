<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Delete Income
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

$id = $_POST['id'] ?? null;

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

/*
|--------------------------------------------------------------------------
| Return to Income
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;