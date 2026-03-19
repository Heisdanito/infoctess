<?php
header("Content-Type: application/json");
require_once '../connection/connection.php';

$query = "SELECT s.student_id, s.student_name, s.programme, s.group_id,
          COUNT(DISTINCT a.session_code) as total_sessions,
          COUNT(a.id) as present_count,
          (SELECT COUNT(DISTINCT session_code) FROM qrcode) - COUNT(DISTINCT a.session_code) as absent_count,
          MAX(a.created_at) as last_attendance
          FROM students s
          LEFT JOIN attendance a ON s.student_id = a.student_id
          WHERE s.active = '1'
          GROUP BY s.student_id
          ORDER BY s.student_name";

$result = mysqli_query($conn, $query);
$students = [];

while ($row = mysqli_fetch_assoc($result)) {
    $students[] = $row;
}

echo json_encode($students);
?>