<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../backend/connection/connection.php';

// Get POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Check if data exists
if (!$data) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No data received'
    ]);
    exit;
}

$username = isset($data['username']) ? mysqli_real_escape_string($conn, $data['username']) : '';
$password = isset($data['password']) ? $data['password'] : '';

// Validate input
if (empty($username) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Username and password required'
    ]);
    exit;
}

// Check if user exists and is admin
$query = "SELECT s.*, p.programme_name 
          FROM students s
          LEFT JOIN programme p ON s.programme = p.programme_code
          WHERE (s.student_id = '$username' OR s.student_mail = '$username') 
          AND s.roles = 'admin' 
          AND s.active = '1'";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
    exit;
}

if (mysqli_num_rows($result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid admin credentials or not authorized'
    ]);
    exit;
}

$user = mysqli_fetch_assoc($result);

// For now, using simple password check (you should implement proper password hashing)
// In production, use password_verify() if you have hashed passwords
if ($password !== 'admin123' && $password !== $user['student_id']) { // Simple check - REPLACE THIS!
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid password'
    ]);
    exit;
}

// Set session variables
$_SESSION['student_id'] = $user['student_id'];
$_SESSION['user_role'] = $user['roles'];
$_SESSION['user_name'] = $user['student_name'];
$_SESSION['admin_authenticated'] = true;
$_SESSION['login_time'] = time();

// Log admin login (optional - if you have an activity log table)
$log_query = "INSERT INTO activity_log (user_id, action, details, ip_address, created_at) 
              VALUES ('{$user['student_id']}', 'admin_login', 'Admin logged in', '{$_SERVER['REMOTE_ADDR']}', NOW())";
mysqli_query($conn, $log_query);

// Return success response
echo json_encode([
    'status' => 'success',
    'message' => 'Login successful',
    'role' => $user['roles'],
    'user' => [
        'id' => $user['student_id'],
        'name' => $user['student_name'],
        'email' => $user['student_mail'],
        'role' => $user['roles'],
        'programme' => $user['programme_name']
    ]
]);

mysqli_close($conn);
?>