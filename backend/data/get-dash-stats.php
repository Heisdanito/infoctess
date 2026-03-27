<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
session_start();

$db_host = "mysql-291ab10a-heisdanito-7ee7.b.aivencloud.com";
$db_user = "avnadmin";
$db_psw  = "AVNS_ZFYiFvpqdF-G5jN0vXu";
$db_name = "defaultdb";
$port    = 21225;
$ca_path = __DIR__ . '/../../ca.pem';

if (!file_exists($ca_path)) { echo json_encode(["status"=>"error","message"=>"ca.pem not found"]); exit; }
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli();
    $conn->ssl_set(NULL, NULL, $ca_path, NULL, NULL);
    $conn->real_connect($db_host, $db_user, $db_psw, $db_name, $port, NULL, MYSQLI_CLIENT_SSL);
} catch (Exception $e) { echo json_encode(["status"=>"error","message"=>$e->getMessage()]); exit; }

$group_id = $_SESSION['group_id'] ?? null;
if (!$group_id) { echo json_encode(["status"=>"error","message"=>"No group in session"]); exit; }

// Total sessions created
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM qrcode WHERE group_id='$group_id'"));
$total_sessions = intval($r['c']);

// Active sessions
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM qrcode WHERE group_id='$group_id' AND is_active='1'"));
$active_sessions = intval($r['c']);

// Total attendance records
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM attendance WHERE group_id='$group_id'"));
$total_attendance = intval($r['c']);

// Total students in group
$r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM students WHERE group_id='$group_id' AND active='1'"));
$total_students = intval($r['c']);

// Per course breakdown
$sql = mysqli_query($conn,
    "SELECT course_id,
            COUNT(*) AS total_att
     FROM attendance
     WHERE group_id = '$group_id'
     GROUP BY course_id"
);
$courses = [];
while ($row = mysqli_fetch_assoc($sql)) {
    $cid      = mysqli_real_escape_string($conn, $row['course_id']);
    $sessions = intval(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM qrcode WHERE group_id='$group_id' AND course_id='$cid'"))['c']);
    $pct      = $sessions > 0 ? round(($row['total_att'] / ($sessions * $total_students)) * 100) : 0;
    $courses[] = [
        "course_id"    => $row['course_id'],
        "attendance"   => intval($row['total_att']),
        "sessions"     => $sessions,
        "percentage"   => $pct
    ];
}

echo json_encode([
    "status"           => "success",
    "total_sessions"   => $total_sessions,
    "active_sessions"  => $active_sessions,
    "total_attendance" => $total_attendance,
    "total_students"   => $total_students,
    "courses"          => $courses
]);
$conn->close();
?>
