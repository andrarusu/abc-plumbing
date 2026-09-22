<?php
session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: staff_login.php');
    exit;
}

header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$_SESSION['staff_csrf'] ??= bin2hex(random_bytes(32));
$error = '';
$staffUsers = [];
$flash = $_SESSION['manage_staff_flash'] ?? '';
unset($_SESSION['manage_staff_flash']);

try {
    $config = require __DIR__ . '/config.local.php';

    $pdo = new PDO(
        $config['dsn'],
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Check the real account in MySQL, not only a username stored in the session.
    // For this demo, administrator1 is the only account allowed to create staff.
    $accountQuery = $pdo->prepare('SELECT username FROM staff_users WHERE id = :id LIMIT 1');
    $accountQuery->execute([':id' => $_SESSION['staff_id']]);
    $currentAccount = $accountQuery->fetch();

    if (!$currentAccount) {
        session_unset();
        session_destroy();
        header('Location: staff_login.php');
        exit;
    }

    if ($currentAccount['username'] !== 'administrator1') {
        http_response_code(403);
        exit('Access denied. Only the administrator can manage staff accounts.');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['staff_csrf'], (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            exit('Invalid request. Please reload the page.');
        }

        $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $confirmation = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

        if (!preg_match('/^[A-Za-z][A-Za-z0-9._-]{2,49}$/D', $username)) {
            $error = 'Username must be 3–50 characters, start with a letter, and use only letters, numbers, dots, underscores or hyphens.';
        } elseif (strlen($password) < 12 || strlen($password) > 128) {
            $error = 'Password must be between 12 and 128 characters.';
        } elseif ($password !== $confirmation) {
            $error = 'The two passwords do not match.';
        } else {
            try {
                $insert = $pdo->prepare(
                    'INSERT INTO staff_users (username, password_hash) VALUES (:username, :password_hash)'
                );
                $insert->execute([
                    ':username' => $username,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                $_SESSION['manage_staff_flash'] = 'Staff account created: ' . $username;
                header('Location: manage_staff.php', true, 303);
                exit;
            } catch (PDOException $exception) {
                if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
                    $error = 'That username is already in use.';
                } else {
                    error_log($exception->getMessage());
                    $error = 'Unable to create the account. Please try again.';
                }
            }
        }
    }

    $staffUsers = $pdo->query(
        'SELECT username, created_at FROM staff_users ORDER BY created_at DESC, username ASC'
    )->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Unable to load staff accounts. Check the local database connection.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Staff | ABC Plumbing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f8fa; color: #18323e; }
        .brand-abc { color: #167d91; font-weight: 800; font-size: 1.7rem; line-height: 1; }
        .brand-subtitle { color: #18323e; font-size: .68rem; font-weight: 700; letter-spacing: .09em; }
        .btn-brand { background: #167d91; border-color: #167d91; color: white; }
        .btn-brand:hover, .btn-brand:focus { background: #0e5968; border-color: #0e5968; color: white; }
    </style>
</head>
<body>
<main class="container py-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom pb-3 mb-4">
        <a href="index.html" class="text-decoration-none d-flex flex-column" aria-label="ABC Plumbing home">
            <span class="brand-abc">ABC</span>
            <span class="brand-subtitle">PLUMBING SERVICES</span>
        </a>
        <a class="btn btn-outline-secondary rounded-pill px-4" href="staff_dashboard.php">← Back to dashboard</a>
    </header>

    <h1 class="h2 fw-bold mb-1">Manage Staff</h1>
    <p class="text-muted mb-4">Create a login for another member of staff. Only administrator1 can access this page.</p>

    <?php if ($flash): ?>
        <div class="alert alert-success" role="status"><?= e($flash) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <section class="col-12 col-lg-6" aria-labelledby="addStaffHeading">
            <div class="bg-white border rounded-4 p-4 h-100">
                <h2 id="addStaffHeading" class="h4 mb-3">Add staff account</h2>
                <form method="post" action="manage_staff.php" autocomplete="off">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['staff_csrf']) ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="username">Username</label>
                        <input class="form-control" id="username" name="username" type="text" minlength="3" maxlength="50" pattern="[A-Za-z][A-Za-z0-9._-]{2,49}" autocomplete="off" value="<?= e($username ?? '') ?>" required>
                        <div class="form-text">3–50 characters; start with a letter.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                        <div class="form-text">At least 12 characters. Do not reuse your administrator password.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="confirm_password">Confirm password</label>
                        <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="12" maxlength="128" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn btn-brand px-4 fw-semibold">Create staff account</button>
                </form>
            </div>
        </section>

        <section class="col-12 col-lg-6" aria-labelledby="staffListHeading">
            <div class="bg-white border rounded-4 p-4 h-100">
                <h2 id="staffListHeading" class="h4 mb-3">Existing staff accounts</h2>
                <?php if (!$staffUsers): ?>
                    <p class="text-muted mb-0">No accounts to show.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th scope="col">Username</th><th scope="col">Added</th></tr></thead>
                            <tbody>
                            <?php foreach ($staffUsers as $user): ?>
                                <tr>
                                    <td><?= e($user['username']) ?><?= $user['username'] === 'administrator1' ? ' <span class="badge text-bg-info">Administrator</span>' : '' ?></td>
                                    <td><?= e($user['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <p class="small text-muted mb-0">Passwords are stored as hashes and are never displayed here.</p>
            </div>
        </section>
    </div>
</main>
</body>
</html>
