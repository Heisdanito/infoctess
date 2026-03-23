<?php
header("Content-Type: application/json");
session_start();
require_once '../../backend/connection/connection.php';

$student_id = $_SESSION['student_id'] ?? null;

$sql = $conn->query("SELECT student_id, roles, programme, group_id 
                     FROM students 
                     WHERE student_id = '$student_id' AND active = '1'");

if ($sql && mysqli_num_rows($sql) > 0) {
    $row = mysqli_fetch_assoc($sql);

    $_SESSION['role']     = $row['roles'];
    $_SESSION['programme'] = $row['programme'];
    $_SESSION['group_id'] = $row['group_id'];

    $student_group = $_SESSION['group_id'];

    // Get all group rows for this student
$sql2 = $conn->query("SELECT * FROM `groups` WHERE `group_id` = '$student_group'");
    if ($sql2 && mysqli_num_rows($sql2) > 0) {
        $courses = [];

        while ($row2 = mysqli_fetch_assoc($sql2)) {
            $courses[] = '<option value="'.$row2['course_id'] .'">'.$row2['course_id'] .'</option>';
        }

      ;

        echo json_encode([
            "status"    => "success",
            "student_id"=> $student_id,
            "role"      => $_SESSION['role'],
            "programme" => $_SESSION['programme'],
            "group_id"  => $_SESSION['group_id'],
            "courses"   => $courses
        ]);
    } else {
        echo json_encode([
            "status"    => "failed",
            "message"   => "No courses found for this group"
        ]);
    }
} else {
    echo json_encode([
        "status"  => "failed",
        "message" => "No active student found"
    ]);
}
?>
