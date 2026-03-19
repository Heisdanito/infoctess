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

require_once '../connection/connection.php';

// Check authentication
if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!$data || !isset($data['students']) || !is_array($data['students'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid data format'
    ]);
    exit;
}

$students = $data['students'];
$programme = isset($data['programme']) ? mysqli_real_escape_string($conn, $data['programme']) : '';
$course = isset($data['course']) ? mysqli_real_escape_string($conn, $data['course']) : '';
$group = isset($data['group']) ? mysqli_real_escape_string($conn, $data['group']) : '';

if (empty($programme)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Programme is required'
    ]);
    exit;
}

// Initialize results
$results = [
    'success' => 0,
    'failed' => 0,
    'duplicates' => 0,
    'total' => count($students),
    'errors' => []
];

// Process each student
foreach ($students as $index => $student) {
    $student_id = mysqli_real_escape_string($conn, trim($student['student_id']));
    $student_name = mysqli_real_escape_string($conn, trim($student['student_name']));
    $student_mail = mysqli_real_escape_string($conn, trim($student['student_mail']));
    $roles = mysqli_real_escape_string($conn, $student['roles'] ?? 'user');
    $active = mysqli_real_escape_string($conn, $student['active'] ?? '1');
    
    // Validate required fields
    if (empty($student_id) || empty($student_name)) {
        $results['failed']++;
        $results['errors'][] = [
            'row' => $index + 2,
            'student_id' => $student_id,
            'name' => $student_name,
            'message' => 'Missing ID or Name'
        ];
        continue;
    }

    // Check if student already exists
    $check_query = "SELECT student_id FROM students WHERE student_id = '$student_id'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing student
        $update_query = "UPDATE students SET 
                        student_name = '$student_name',
                        student_mail = '$student_mail',
                        programme = '$programme',
                        group_id = " . ($group ? "'$group'" : "NULL") . ",
                        roles = '$roles',
                        active = '$active',
                        updated_at = NOW()
                        WHERE student_id = '$student_id'";
        
        if (mysqli_query($conn, $update_query)) {
            $results['success']++;
        } else {
            $results['failed']++;
            $results['errors'][] = [
                'row' => $index + 2,
                'student_id' => $student_id,
                'name' => $student_name,
                'message' => 'Update failed: ' . mysqli_error($conn)
            ];
        }
    } else {
        // Insert new student
        $insert_query = "INSERT INTO students 
                        (student_id, student_name, student_mail, programme, group_id, roles, active, created_at, updated_at) 
                        VALUES 
                        ('$student_id', '$student_name', '$student_mail', '$programme', " . 
                        ($group ? "'$group'" : "NULL") . ", '$roles', '$active', NOW(), NOW())";
        
        if (mysqli_query($conn, $insert_query)) {
            $results['success']++;
        } else {
            // Check if duplicate
            if (mysqli_errno($conn) == 1062) { // Duplicate entry error
                $results['duplicates']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 2,
                    'student_id' => $student_id,
                    'name' => $student_name,
                    'message' => 'Insert failed: ' . mysqli_error($conn)
                ];
            }
        }
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Bulk upload completed',
    'data' => $results
]);

mysqli_close($conn);
?>