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

// Verify admin session
if (!isset($_SESSION['student_id']) || !isset($_SESSION['admin_authenticated'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Verify admin role
$admin_check = mysqli_query($conn, "SELECT roles FROM students WHERE student_id = '{$_SESSION['student_id']}' AND active = '1'");
$admin_data = mysqli_fetch_assoc($admin_check);

if (!$admin_data || $admin_data['roles'] !== 'admin') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Admin privileges required'
    ]);
    exit;
}

// Initialize stats array
$stats = [];

// 1. Total students
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE active = '1'");
$stats['total_students'] = intval(mysqli_fetch_assoc($result)['count']);

// 2. Total courses
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses");
$stats['total_courses'] = intval(mysqli_fetch_assoc($result)['count']);

// 3. Active sessions
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM qrcode WHERE is_active = '1'");
$stats['active_sessions'] = intval(mysqli_fetch_assoc($result)['count']);

// 4. Today's attendance
$today = date('Y-m-d');
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE DATE(created_at) = '$today'");
$stats['today_attendance'] = intval(mysqli_fetch_assoc($result)['count']);

// 5. Total attendance all time
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance");
$stats['total_attendance'] = intval(mysqli_fetch_assoc($result)['count']);

// 6. Total groups
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM group_main");
$stats['total_groups'] = intval(mysqli_fetch_assoc($result)['count']);

// 7. Total programmes
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM programme");
$stats['total_programmes'] = intval(mysqli_fetch_assoc($result)['count']);

// 8. Total QR sessions (all time)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM qrcode");
$stats['total_sessions'] = intval(mysqli_fetch_assoc($result)['count']);

// 9. Attendance by role today
$role_stats = [];
$roles = ['admin', 'rep', 'ta', 'lec', 'user'];
foreach ($roles as $role) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count 
                                   FROM attendance a 
                                   JOIN students s ON a.student_id = s.student_id 
                                   WHERE DATE(a.created_at) = '$today' AND s.roles = '$role'");
    $role_stats[$role] = intval(mysqli_fetch_assoc($result)['count']);
}
$stats['attendance_by_role_today'] = $role_stats;

// 10. Recent activity (last 10 attendance records)
$recent_query = "SELECT a.*, s.student_name, c.course_name 
                FROM attendance a
                JOIN students s ON a.student_id = s.student_id
                LEFT JOIN qrcode q ON a.session_code = q.session_code
                LEFT JOIN courses c ON q.course_id = c.course_id
                ORDER BY a.created_at DESC 
                LIMIT 10";
$recent_result = mysqli_query($conn, $recent_query);
$recent_activity = [];
while ($row = mysqli_fetch_assoc($recent_result)) {
    $recent_activity[] = [
        'time' => $row['created_at'],
        'student_name' => $row['student_name'],
        'student_id' => $row['student_id'],
        'session_code' => $row['session_code'],
        'course_name' => $row['course_name']
    ];
}
$stats['recent_activity'] = $recent_activity;

// 11. Last 7 days attendance trend
$trend_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM attendance WHERE DATE(created_at) = '$date'");
    $trend_data[] = [
        'date' => $date,
        'count' => intval(mysqli_fetch_assoc($result)['count'])
    ];
}
$stats['last_7_days'] = $trend_data;

// 12. Top 5 courses by attendance
$top_courses_query = "SELECT c.course_id, c.course_name, COUNT(a.id) as attendance_count
                      FROM courses c
                      LEFT JOIN qrcode q ON c.course_id = q.course_id
                      LEFT JOIN attendance a ON q.session_code = a.session_code
                      GROUP BY c.course_id
                      ORDER BY attendance_count DESC
                      LIMIT 5";
$top_courses_result = mysqli_query($conn, $top_courses_query);
$top_courses = [];
while ($row = mysqli_fetch_assoc($top_courses_result)) {
    $top_courses[] = $row;
}
$stats['top_courses'] = $top_courses;

// 13. User distribution by role
$role_distribution = [];
foreach ($roles as $role) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students WHERE roles = '$role' AND active = '1'");
    $role_distribution[$role] = intval(mysqli_fetch_assoc($result)['count']);
}
$stats['user_distribution'] = $role_distribution;

// 14. Session status breakdown
$session_stats_query = "SELECT 
                        SUM(CASE WHEN is_active = '1' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN is_active = '0' THEN 1 ELSE 0 END) as inactive,
                        SUM(CASE WHEN expire_at < NOW() AND is_active = '1' THEN 1 ELSE 0 END) as expired
                        FROM qrcode";
$session_stats_result = mysqli_query($conn, $session_stats_query);
$session_stats = mysqli_fetch_assoc($session_stats_result);
$stats['session_status'] = [
    'active' => intval($session_stats['active']),
    'inactive' => intval($session_stats['inactive']),
    'expired' => intval($session_stats['expired'])
];

echo json_encode([
    'status' => 'success',
    'data' => $stats
]);

mysqli_close($conn);
?>