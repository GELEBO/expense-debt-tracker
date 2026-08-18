<?php

require_once "../config/database.php";
require_once "../includes/interest.php";

$debtId = $_GET['id'] ?? null;

if (!$debtId) {
    die("Debt ID is required.");
}

$stmt = $pdo->prepare("
    SELECT
        id,
        creditor,
        description,
        original_amount,
        interest_rate,
        interest_period,
        interest_start_date,
        due_date,
        status
    FROM debts
    WHERE id = :id
");

$stmt->execute([
    ":id" => $debtId
]);

$debt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$debt) {
    die("Debt not found.");
}

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount), 0)
    FROM debt_payments
    WHERE debt_id = :debt_id
");

$stmt->execute([
    ":debt_id" => $debtId
]);

$totalPaid = (float) $stmt->fetchColumn();

$remainingPrincipal =
    (float) $debt['original_amount'] - $totalPaid;

$elapsedDays = calculateElapsedDays(
    $debt['interest_start_date']
);

$accruedInterest = calculateAccruedInterest(
    $remainingPrincipal,
    (float) $debt['interest_rate'],
    $debt['interest_period'],
    $elapsedDays
);

$totalOwed = $remainingPrincipal + $accruedInterest;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Debt Details</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <h1>Debt Details</h1>

    <p>
        <a href="index.php">← Back to Debts</a>
    </p>

    <h2><?php echo htmlspecialchars($debt['creditor']); ?></h2>

    <p>
        <?php echo htmlspecialchars($debt['description'] ?? ''); ?>
    </p>

    <hr>

    <p>
        <strong>Original Debt:</strong>
        <?php echo number_format($debt['original_amount'], 2); ?> ETB
    </p>

    <p>
        <strong>Total Paid:</strong>
        <?php echo number_format($totalPaid, 2); ?> ETB
    </p>

    <p>
        <strong>Remaining Principal:</strong>
        <?php echo number_format($remainingPrincipal, 2); ?> ETB
    </p>

    <p>
        <strong>Interest Rate:</strong>
        <?php echo number_format($debt['interest_rate'], 2); ?>%
        <?php echo htmlspecialchars($debt['interest_period']); ?>
    </p>

    <p>
        <strong>Interest Start Date:</strong>
        <?php echo htmlspecialchars($debt['interest_start_date']); ?>
    </p>

    <p>
        <strong>Elapsed Days:</strong>
        <?php echo $elapsedDays; ?> days
    </p>

    <p>
        <strong>Accrued Interest:</strong>
        <?php echo number_format($accruedInterest, 2); ?> ETB
    </p>

    <p>
        <strong>Total Amount Owed:</strong>
        <?php echo number_format($totalOwed, 2); ?> ETB
    </p>

    <p>
        <strong>Due Date:</strong>
        <?php echo htmlspecialchars($debt['due_date']); ?>
    </p>

    <p>
        <strong>Status:</strong>
        <?php echo htmlspecialchars($debt['status']); ?>
    </p>

</body>
</html>