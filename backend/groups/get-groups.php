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

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required',
        'data' => []
    ]);
    exit;
}

// Get filter parameters
$programme_id = isset($_GET['programme_id']) ? mysqli_real_escape_string($conn, $_GET['programme_id']) : '';
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query - Get groups with related information
$query = "SELECT g.*, 
          gm.group_name as main_group_name,
          p.programme_name,
          (SELECT COUNT(*) FROM students s WHERE s.group_id = g.group_id AND s.active = '1') as student_count,
          (SELECT COUNT(*) FROM qrcode q WHERE q.group_id = g.group_id) as session_count,
          (SELECT COUNT(*) FROM attendance a WHERE a.group_id = g.group_id) as attendance_count
          FROM `groups` g
          LEFT JOIN group_main gm ON g.group_id = gm.group_id
          LEFT JOIN programme p ON g.programme_id = p.programme_code
          WHERE 1=1";

// Add filters
if (!empty($programme_id)) {
    $query .= " AND g.programme_id = '$programme_id'";
}

if (!empty($status)) {
    $query .= " AND g.status = '$status'";
}

if (!empty($search)) {
    $query .= " AND (g.group_name LIKE '%$search%' OR g.group_id LIKE '%$search%')";
}

$query .= " ORDER BY g.created_at DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . mysqli_error($conn),
        'data' => []
    ]);
    exit;
}

$groups = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Get course names for this group if course_id exists
    $courses = [];
    if (!empty($row['course_id'])) {
        $course_ids = explode(',', $row['course_id']);
        foreach ($course_ids as $cid) {
            $course_query = "SELECT course_id, course_name FROM courses WHERE course_id = '$cid'";
            $course_result = mysqli_query($conn, $course_query);
            if ($course_row = mysqli_fetch_assoc($course_result)) {
                $courses[] = $course_row;
            }
        }
    }
    
    // Get group representatives info
    $representatives = [];
    if (!empty($row['group_rep_id'])) {
        $rep_query = "SELECT student_id, student_name FROM students WHERE student_id = '{$row['group_rep_id']}'";
        $rep_result = mysqli_query($conn, $rep_query);
        if ($rep_row = mysqli_fetch_assoc($rep_result)) {
            $representatives[] = $rep_row;
        }
    }
    if (!empty($row['group_rep_id_2'])) {
        $rep_query = "SELECT student_id, student_name FROM students WHERE student_id = '{$row['group_rep_id_2']}'";
        $rep_result = mysqli_query($conn, $rep_query);
        if ($rep_row = mysqli_fetch_assoc($rep_result)) {
            $representatives[] = $rep_row;
        }
    }
    
    $groups[] = [
        'id' => $row['id'],
        'group_id' => $row['group_id'],
        'group_name' => $row['group_name'],
        'main_group_name' => $row['main_group_name'],
        'programme_id' => $row['programme_id'],
        'programme_name' => $row['programme_name'],
        'course_id' => $row['course_id'],
        'courses' => $courses,
        'status' => $row['status'],
        'group_rep_id' => $row['group_rep_id'],
        'group_rep_id_2' => $row['group_rep_id_2'],
        'representatives' => $representatives,
        'academic_year' => $row['academic_year'],
        'student_count' => intval($row['student_count']),
        'session_count' => intval($row['session_count']),
        'attendance_count' => intval($row['attendance_count']),
        'created_at' => $row['created_at'],
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($groups),
    'data' => $groups
]);

mysqli_close($conn);
?>