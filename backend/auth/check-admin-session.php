<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../connection/connection.php';

// Initialize response
$response = [
    'authenticated' => false,
    'role' => null,
    'user' => null,
    'message' => ''
];

// Check if session exists
if (!isset($_SESSION['student_id'])) {
    $response['message'] = 'No session found';
    echo json_encode($response);
    exit;
}

$student_id = mysqli_real_escape_string($conn, $_SESSION['student_id']);

// Query to check if user is admin and active
$query = "SELECT student_id, student_name, student_mail, roles, programme, active 
          FROM students 
          WHERE student_id = '$student_id' AND active = '1'";

$result = mysqli_query($conn, $query);

if (!$result) {
    $response['message'] = 'Database error: ' . mysqli_error($conn);
    echo json_encode($response);
    exit;
}

if (mysqli_num_rows($result) === 0) {
    // User not found or inactive
    session_destroy();
    $response['message'] = 'User not found or inactive';
    echo json_encode($response);
    exit;
}

$user = mysqli_fetch_assoc($result);

// Check if user has admin role
if ($user['roles'] !== 'admin') {
    $response['message'] = 'User is not an admin';
    echo json_encode($response);
    exit;
}

// User is authenticated admin
$response['authenticated'] = true;
$response['role'] = $user['roles'];
$response['user'] = [
    'id' => $user['student_id'],
    'name' => $user['student_name'],
    'email' => $user['student_mail'],
    'role' => $user['roles'],
    'programme' => $user['programme']
];
$response['message'] = 'Session valid';

echo json_encode($response);

mysqli_close($conn);
?>