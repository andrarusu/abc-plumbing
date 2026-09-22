<?php
header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$postcode = strtoupper(trim($_POST['postcode'] ?? ''));
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');
$contactMethod = trim($_POST['contactMethod'] ?? '');

$allowedServices = [
    'Leak Repairs',
    'Drain Cleaning',
    'Boiler Service',
    'Bathroom Plumbing',
    'Pipe Installation',
    'Emergency Plumbing',
    'Other'
];

$cleanPhone = preg_replace('/[\s()-]/', '', $phone);

if (
    strlen($name) < 2 ||
    strlen($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($email) > 255 ||
    !preg_match('/^(?:0\d{10}|\+44\d{10})$/', $cleanPhone) ||
    !preg_match('/^(GIR ?0AA|[A-Z]{1,2}[0-9][0-9A-Z]? ?[0-9][A-Z]{2})$/', $postcode) ||
    !in_array($service, $allowedServices, true) ||
    strlen($message) < 10 ||
    !in_array($contactMethod, ['Email', 'Phone'], true)
) {
    respond(400, [
        'success' => false,
        'message' => 'Please check the information entered in the form.'
    ]);
}

try {
    $config = require __DIR__ . '/config.local.php';

    $pdo = new PDO(
        $config['dsn'],
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $pdo->prepare(
        'INSERT INTO quote_requests
        (customer_name, email, phone, postcode, service, message, contact_method)
        VALUES
        (:name, :email, :phone, :postcode, :service, :message, :contact_method)'
    );

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':postcode' => $postcode,
        ':service' => $service,
        ':message' => $message,
        ':contact_method' => $contactMethod
    ]);

    respond(201, [
        'success' => true,
        'message' => 'Your demo quote request has been saved.'
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());

    respond(500, [
        'success' => false,
        'message' => 'Unable to save the request. Please try again.'
    ]);
}