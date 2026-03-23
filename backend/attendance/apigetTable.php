<?php 
header("Content-Type: application/json");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
session_start();
//verify connection
require_once '../../backend/connection/connection.php';
$session_code =  $_SESSION['qr_session'] ?? null;
$student_id = $_SESSION['student_id'] ?? 5262140032 ;
$session_course = $_SESSION['Activecourse']  ?? null ;

$stmt_bounce = $conn->query("SELECT QRcode , is_active , session_code 
FROM qrcode WHERE QRcode = '$session_code' 
OR session_code = '$session_code'  AND is_active = 0 ");

if (mysqli_num_rows($stmt_bounce) === 1) {
    echo json_encode([
        "status" => "failed",
        "data" => "<tr class='new-row'><td>
                <div class='font-weight-bold text-danger mt-1'>No Active course found</div>
            </td></tr>"
    ]);
    exit;
}

// query made to  Validate student
$sql = $conn->query("SELECT group_id, programme, roles 
                     FROM students 
                     WHERE student_id = '$student_id' AND active = '1'");
if (mysqli_num_rows($sql) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Student not active or not found"
    ]);
    exit;
}
$student = mysqli_fetch_assoc($sql);
$student_group_id = $student['group_id'];
$student_programme = $student['programme'];

// Verify group programme
$sql_group = $conn->query("SELECT group_id , programme_id FROM `groups`
                           WHERE group_id = '$student_group_id'
                           AND programme_id = '$student_programme'  ");

if (mysqli_num_rows($sql_group) === 0) {
    echo json_encode([
        "status" => "failed",
        "message" => "Group programme mismatch or inactive group " . $student_programme ,
        "data" => "<tr class='new-row'><td>
                <div class='font-weight-bold text-danger mt-1'>Group programme mismatch or inactive group</div>
            </td></tr>"
    ]);
    exit;
}

//get table infotess attendance if user already exist in current attendance list and active for usage
$sql_check_att = $conn->query("SELECT * FROM attendance 
                               WHERE session_code = '$session_code'
                               AND  group_id = '$student_group_id' 
                               AND course_id = '$session_course'
                                ");
//return out put                
$student_tr = [];
if (mysqli_num_rows($sql_check_att) > 0) {
    while($row = mysqli_fetch_assoc($sql_check_att)){
        // Convert created_at into relative time
        $created_at = new DateTime($row['created_at']);
        $now = new DateTime();
        $diff = $now->diff($created_at);
    
        if ($diff->y > 0) {
            $timeAgo = $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
        } elseif ($diff->m > 0) {
            $timeAgo = $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
        } elseif ($diff->d > 0) {
            $timeAgo = $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
        } elseif ($diff->h > 0) {
            $timeAgo = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
        } elseif ($diff->i > 0) {
            $timeAgo = $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
        } else {
            $timeAgo = "just now";
        }
    
        $student_tr[] = '
        <tr class="new-row">
            <td>
                <div class="d-flex">
                <img class="img-sm rounded-circle mb-md-0 mr-2" src="../images/faces/image01.jpg" alt="profile image">
                <div>
                    <div>Name</div>
                    <div class="font-weight-bold mt-1">'.$row['student_id'].'</div>
                </div>
                </div>
            </td>
            <td>
                course
                <div class="font-weight-bold  mt-1">'.$row['course_id'].'</div>
            </td>
            <td>
                Status
                <div class="font-weight-bold text-success  mt-1">'.$row['serial'].'</div>
            </td>
            <td>
                Progress
                <div class="font-weight-bold text-danger mt-1">Not avai%</div>
            </td>
            <td>
                Created at
                <div class="font-weight-bold  mt-1">'.$timeAgo.'</div>
            </td>
            <td>
                <button type="button" class="btn btn-sm text-white bg-danger">Unregister</button>
            </td>
        </tr>
        ';
    }
    

        echo json_encode([
        "status" => "success",
        "data" => $student_tr,
        "course" => $session_course
            ]);
    exit;

} else{
    echo json_encode([
        "status" => "failed",
        "data" => "<tr class='new-row'><td>
                <div class='font-weight-bold text-danger mt-1'>No data found in the databse for current section or try again by activating your section</div>
            </td></tr>"
    ]);
}
}else{
    echo json_encode([
        "status" => "Bad gateway",
        "data" => "Post request only",
        ]);
    exit;
}
