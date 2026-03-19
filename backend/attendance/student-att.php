<?php
header("Content-Type: application/json");
require_once '../../backend/connection/connection.php';
session_start();

$student_id = $_SESSION['student_id'] ?? null;

$sql = $conn->query("SELECT course_id, COUNT(*) as attended_classes
                     FROM attendance
                     WHERE student_id = '$student_id'
                     GROUP BY course_id");

if (mysqli_num_rows($sql) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "No attendance records found"
    ]);
    exit;
}

$course_stats = [];
$total_classes = 0;
$total_attended = 0;

while ($row = mysqli_fetch_assoc($sql)) {
    $course_id = $row['course_id'];
    $attended = $row['attended_classes'];

    // Query total sessions for this course (all students)
    $sql_total = $conn->query("SELECT COUNT(*) as total_sessions
                               FROM attendance
                               WHERE course_id = '$course_id'");
    $total_sessions = mysqli_fetch_assoc($sql_total)['total_sessions'];

    $percentage = $total_sessions > 0 
        ? round(($attended / $total_sessions) * 100, 2) 
        : 0;

    $course_stats[] = [
        "course_id" => $course_id,
        "total_sessions" => $total_sessions,
        "attended" => $attended,
        "missed" => $total_sessions - $attended,
        "percentage" => $percentage
    ];

    $courses_table[] = [
        "data" => '
            <div class="d-flex justify-content-between mb-4">
            <div class="text-secondary font-weight-medium">'. $course_id .'</div>
            <div class="small"><h3>'.$attended .'</h3></div>
            <div class="small"><h3>'.$total_sessions - $attended.'</h3></div>
            <div class="small"><h3>'.$total_sessions.'</h3></div>
            <div class="small"><h3>'.$percentage.'%</h3></div>
            </div>
        ',
    ];

    $total_classes += $total_sessions;
    $total_attended += $attended;
}

// Overall percentage
$total_missed = $total_classes - $total_attended;
$overall_percentage = $total_classes > 0 
    ? round(($total_attended / $total_classes) * 100, 2) 
    : 0;





echo json_encode([
    "status" => "success",
    "overall" => [
        "total_classes" => $total_classes,
        "attended" => $total_attended,
        "missed" => $total_missed,
        "percentage" => $overall_percentage
    ],
    "courses" => $course_stats,
    "data" => $courses_table
]);
exit;
