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
        'message' => 'Authentication required'
    ]);
    exit;
}

// Get group ID
$group_id = isset($_GET['group_id']) ? mysqli_real_escape_string($conn, $_GET['group_id']) : '';

if (empty($group_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Group ID required'
    ]);
    exit;
}

// Get group details
$query = "SELECT g.*, 
          gm.group_name as main_group_name,
          p.programme_name,
          (SELECT COUNT(*) FROM students s WHERE s.group_id = g.group_id AND s.active = '1') as student_count
          FROM `groups` g
          LEFT JOIN group_main gm ON g.group_id = gm.group_id
          LEFT JOIN programme p ON g.programme_id = p.programme_code
          WHERE g.group_id = '$group_id' OR g.id = '$group_id'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Group not found'
    ]);
    exit;
}

$row = mysqli_fetch_assoc($result);

// Get students in this group
$students_query = "SELECT student_id, student_name, student_mail, roles 
                   FROM students 
                   WHERE group_id = '{$row['group_id']}' AND active = '1'
                   ORDER BY student_name";
$students_result = mysqli_query($conn, $students_query);
$students = [];
while ($student = mysqli_fetch_assoc($students_result)) {
    $students[] = $student;
}

// Get courses for this group
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

// Get recent sessions for this group
$sessions_query = "SELECT q.*, c.course_name,
                   (SELECT COUNT(*) FROM attendance a WHERE a.session_code = q.session_code) as scan_count
                   FROM qrcode q
                   LEFT JOIN courses c ON q.course_id = c.course_id
                   WHERE q.group_id = '{$row['group_id']}'
                   ORDER BY q.created_at DESC
                   LIMIT 10";
$sessions_result = mysqli_query($conn, $sessions_query);
$recent_sessions = [];
while ($session = mysqli_fetch_assoc($sessions_result)) {
    $recent_sessions[] = $session;
}

$group = [
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
    'academic_year' => $row['academic_year'],
    'student_count' => intval($row['student_count']),
    'students' => $students,
    'recent_sessions' => $recent_sessions,
    'created_at' => $row['created_at'],
    'updated_at' => $row['updated_at']
];

echo json_encode([
    'status' => 'success',
    'data' => $group
]);

mysqli_close($conn);
?>