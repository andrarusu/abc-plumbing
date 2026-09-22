<?php
session_start();

if (!isset($_SESSION['staff_id'])) {
    header('Location: staff_login.php');
    exit;
}

header('Cache-Control: no-store');

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$statuses = ['New', 'In progress', 'Quoted', 'Completed'];
$_SESSION['staff_csrf'] ??= bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    if (!hash_equals($_SESSION['staff_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Invalid request.');
    }

    session_unset();
    session_destroy();
    header('Location: staff_login.php');
    exit;
}

$requests = [];
$filteredRequests = [];
$error = '';
$flash = $_SESSION['staff_flash'] ?? '';
unset($_SESSION['staff_flash']);

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

    // Read the table's primary key rather than guessing its column name.
    $columns = $pdo->query('SHOW COLUMNS FROM quote_requests')->fetchAll();
    $primaryKeys = [];
    $hasStatus = false;
    foreach ($columns as $column) {
        if ($column['Key'] === 'PRI') {
            $primaryKeys[] = $column['Field'];
        }
        if ($column['Field'] === 'status') {
            $hasStatus = true;
        }
    }

    if (count($primaryKeys) !== 1 || !$hasStatus) {
        throw new RuntimeException('Quote table needs one primary key and the status column.');
    }

    // The identifier comes only from database metadata, not from a visitor.
    $primaryKeySql = '`' . str_replace('`', '``', $primaryKeys[0]) . '`';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        if (!hash_equals($_SESSION['staff_csrf'], (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            exit('Invalid request.');
        }

        $newStatus = $_POST['status'] ?? '';
        $requestId = $_POST['request_id'] ?? '';
        if (!is_string($newStatus) || !in_array($newStatus, $statuses, true) ||
            !is_string($requestId) || !ctype_digit($requestId) ||
            strlen($requestId) > 18 || (int) $requestId < 1) {
            http_response_code(400);
            exit('Invalid request.');
        }

        $update = $pdo->prepare(
            "UPDATE quote_requests SET status = :status WHERE $primaryKeySql = :request_id"
        );
        $update->execute([
            ':status' => $newStatus,
            ':request_id' => $requestId
        ]);

        $_SESSION['staff_flash'] = 'Request status saved.';
        header('Location: staff_dashboard.php', true, 303);
        exit;
    }

    $requests = $pdo->query(
        "SELECT *, $primaryKeySql AS __quote_pk FROM quote_requests ORDER BY $primaryKeySql DESC"
    )->fetchAll();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $error = 'Could not load or update the requests. Check that the SQL step was completed.';
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$search = substr($search, 0, 100);
$statusFilter = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
if (!in_array($statusFilter, $statuses, true)) {
    $statusFilter = '';
}

$counts = array_fill_keys($statuses, 0);
foreach ($requests as $request) {
    if (isset($counts[$request['status']])) {
        $counts[$request['status']]++;
    }
    if ($statusFilter !== '' && $request['status'] !== $statusFilter) {
        continue;
    }
    $searchable = implode(' ', [
        $request['customer_name'], $request['email'], $request['phone'],
        $request['postcode'], $request['service']
    ]);
    if ($search !== '' && stripos($searchable, $search) === false) {
        continue;
    }
    $filteredRequests[] = $request;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Dashboard | ABC Plumbing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f8fa; color: #18323e; }
        .brand-abc { color: #167d91; font-weight: 800; font-size: 1.7rem; line-height: 1; }
        .brand-subtitle { color: #18323e; font-size: .68rem; font-weight: 700; letter-spacing: .09em; }
        .summary-card { background: white; border: 1px solid #e2edf0; border-radius: 1rem; padding: 1rem; height: 100%; }
        .summary-card strong { font-size: 1.65rem; display: block; }
        .table thead th { white-space: nowrap; color: #486573; font-size: .85rem; }
        .message-cell { min-width: 220px; max-width: 350px; white-space: pre-wrap; overflow-wrap: anywhere; }
        .status-form { min-width: 190px; }
        .btn-brand { background: #167d91; border-color: #167d91; color: white; }
        .btn-brand:hover, .btn-brand:focus { background: #0e5968; border-color: #0e5968; color: white; }
    </style>
</head>
<body>
<main class="container-fluid px-3 px-md-5 py-4">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom pb-3 mb-4">
        <a href="index.html" class="text-decoration-none d-flex flex-column" aria-label="ABC Plumbing home">
            <span class="brand-abc">ABC</span>
            <span class="brand-subtitle">PLUMBING SERVICES</span>
        </a>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <?php if (($_SESSION['staff_username'] ?? '') === 'administrator1'): ?>
                <a href="manage_staff.php" class="btn btn-brand rounded-pill px-4 fw-semibold">Manage Staff</a>
            <?php endif; ?>
            <form method="post" class="m-0">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['staff_csrf']) ?>">
                <button type="submit" name="logout" value="1" class="btn btn-outline-danger rounded-pill px-4 fw-semibold">Log out</button>
            </form>
        </div>
    </header>

    <div class="mb-4">
        <h1 class="h2 fw-bold mb-1">Staff Dashboard</h1>
        <p class="text-muted mb-0">Signed in as <?= e($_SESSION['staff_username'] ?? '') ?></p>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-success" role="status"><?= e($flash) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-lg-5 g-3 mb-4">
            <div class="col"><div class="summary-card"><span class="text-muted">All requests</span><strong><?= count($requests) ?></strong></div></div>
            <?php foreach ($statuses as $item): ?>
                <div class="col"><div class="summary-card"><span class="text-muted"><?= e($item) ?></span><strong><?= $counts[$item] ?></strong></div></div>
            <?php endforeach; ?>
        </div>

        <section class="bg-white border rounded-4 p-3 p-md-4 mb-4" aria-label="Find quote requests">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-12 col-md-6">
                    <label for="search" class="form-label fw-semibold">Search requests</label>
                    <input id="search" name="q" class="form-control" maxlength="100" value="<?= e($search) ?>" placeholder="Name, email, phone, postcode or service">
                </div>
                <div class="col-12 col-md-3">
                    <label for="statusFilter" class="form-label fw-semibold">Status</label>
                    <select id="statusFilter" name="status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach ($statuses as $item): ?>
                            <option value="<?= e($item) ?>" <?= $statusFilter === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button class="btn btn-brand flex-fill" type="submit">Search</button>
                    <a href="staff_dashboard.php" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </section>

        <h2 class="h4 mb-3">Quote requests <span class="text-muted fs-6">(<?= count($filteredRequests) ?> shown)</span></h2>
        <?php if (!$filteredRequests): ?>
            <div class="alert alert-info">No requests match your search or filter.</div>
        <?php else: ?>
            <div class="table-responsive bg-white rounded-4 border shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Postcode</th>
                        <th scope="col">Service</th>
                        <th scope="col">Message</th>
                        <th scope="col">Contact by</th>
                        <th scope="col">Status</th>
                        <th scope="col">Details</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($filteredRequests as $request): ?>
                        <tr>
                            <td><?= e($request['customer_name']) ?></td>
                            <td><?= e($request['email']) ?></td>
                            <td><?= e($request['phone']) ?></td>
                            <td><?= e($request['postcode']) ?></td>
                            <td><?= e($request['service']) ?></td>
                            <td class="message-cell"><?= e($request['message']) ?></td>
                            <td><?= e($request['contact_method']) ?></td>
                            <td>
                                <form method="post" class="status-form d-flex flex-column gap-2">
                                    <input type="hidden" name="csrf" value="<?= e($_SESSION['staff_csrf']) ?>">
                                    <input type="hidden" name="request_id" value="<?= e($request['__quote_pk']) ?>">
                                    <select name="status" class="form-select form-select-sm" aria-label="Status for <?= e($request['customer_name']) ?>">
                                        <?php foreach ($statuses as $item): ?>
                                            <option value="<?= e($item) ?>" <?= $request['status'] === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" value="1" class="btn btn-brand btn-sm">Save status</button>
                                </form>
                            </td>
                            <td><a class="btn btn-outline-secondary btn-sm text-nowrap" href="quote_details.php?id=<?= rawurlencode((string) $request['__quote_pk']) ?>">View details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
