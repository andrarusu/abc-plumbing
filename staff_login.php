<?php
session_start();
if (isset($_SESSION['staff_id'])) {
    header('Location: staff_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $config = require __DIR__ . '/config.local.php';

        $pdo = new PDO(
            $config['dsn'],
            $config['username'],
            $config['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash
             FROM staff_users
             WHERE username = :username
             LIMIT 1'
        );

        $stmt->execute([':username' => $username]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($staff && password_verify($password, $staff['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_username'] = $staff['username'];
            header('Location: staff_dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $error = 'Unable to sign in. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Login | ABC Plumbing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <main class="container py-4">

        <header class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-5">
            <a href="index.html" class="text-decoration-none d-flex flex-column">
                <span class="fw-bold fs-3" style="color: #167d91;">ABC</span>
                <span class="small fw-semibold text-dark">PLUMBING SERVICES</span>
            </a>

            <span class="text-muted fw-semibold">Staff Login</span>
        </header>

        <div class="card shadow-sm border-0 mx-auto" style="max-width: 440px;">
            <div class="card-body p-4 p-md-5">
                <h1 class="h3 mb-4">Staff Login</h1>

                <?php if (isset($_SESSION['staff_id'])): ?>
                    <p>Signed in as
                        <strong><?= htmlspecialchars($_SESSION['staff_username'], ENT_QUOTES, 'UTF-8') ?></strong>.
                    </p>
                    <a href="staff_dashboard.php" class="btn btn-primary">Go to dashboard</a>
                <?php else: ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <label for="username" class="form-label">Username</label>
                        <input
                            id="username"
                            name="username"
                            class="form-control mb-3"
                            autocomplete="username"
                            required
                        >

                        <label for="password" class="form-label">Password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control mb-4"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="submit"
                            class="btn w-100 text-white fw-semibold"
                            style="background-color: #167d91;"
                        >
                            Sign in
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </main>
</body>
</html>