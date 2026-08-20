<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Register User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {
        die("Please fill in all required fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    if (strlen($password) < 6) {
        die("Password must be at least 6 characters.");
    }

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    /*
    |--------------------------------------------------------------------------
    | Check Existing Email
    |--------------------------------------------------------------------------
    */

    $checkStmt = $pdo->prepare("
        SELECT id
        FROM users
        WHERE email = ?
    ");

    $checkStmt->execute([$email]);

    if ($checkStmt->fetch()) {
        die("An account with this email already exists.");
    }

    /*
    |--------------------------------------------------------------------------
    | Hash Password
    |--------------------------------------------------------------------------
    */

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    /*
    |--------------------------------------------------------------------------
    | Insert User
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO users
        (
            name,
            email,
            password
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $name,
        $email,
        $hashedPassword
    ]);

    /*
    |--------------------------------------------------------------------------
    | Registration Successful
    |--------------------------------------------------------------------------
    */

    header("Location: login.php");
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

    <title>Register | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Create Account</h1>

    <p>
        Register for the Expense & Debt Tracker.
    </p>

    <form method="POST">

        <div>

            <label for="name">
                Name:
            </label>

            <input
                type="text"
                id="name"
                name="name"
                required
            >

        </div>

        <br>

        <div>

            <label for="email">
                Email:
            </label>

            <input
                type="email"
                id="email"
                name="email"
                required
            >

        </div>

        <br>

        <div>

            <label for="password">
                Password:
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>

        <br>

        <div>

            <label for="confirm_password">
                Confirm Password:
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
            >

        </div>

        <br>

        <button type="submit">
            Register
        </button>

    </form>

    <p>
        Already have an account?
        <a href="login.php">
            Login
        </a>
    </p>

</body>

</html>