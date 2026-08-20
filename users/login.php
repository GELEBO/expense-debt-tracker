<?php

session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Login User
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($email) ||
        empty($password)
    ) {
        die("Please enter your email and password.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    /*
    |--------------------------------------------------------------------------
    | Find User
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT
            id,
            name,
            email,
            password
        FROM users
        WHERE email = ?
    ");

    $stmt->execute([$email]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Verify Password
    |--------------------------------------------------------------------------
    */

    if (
        !$user ||
        !password_verify($password, $user['password'])
    ) {
        die("Invalid email or password.");
    }

    /*
    |--------------------------------------------------------------------------
    | Create Session
    |--------------------------------------------------------------------------
    */

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    /*
    |--------------------------------------------------------------------------
    | Login Successful
    |--------------------------------------------------------------------------
    */

    header("Location: ../index.php");
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

    <title>Login | Expense & Debt Tracker</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

    <h1>Login</h1>

    <p>
        Login to your Expense & Debt Tracker account.
    </p>

    <form method="POST">

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

        <button type="submit">
            Login
        </button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">
            Register
        </a>
    </p>

</body>

</html>