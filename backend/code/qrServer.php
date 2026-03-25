<?php
use Dom\Mysql;

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    session_start();    
    require_once '../../backend/connection/connection.php';

    $student_id    = $_SESSION['student_id'] ?? null;
    $currentCourse = $_SESSION['Activecourse'] ?? null ;

    // Capture location from JSON body
    // $input     = json_decode(file_get_contents("php://input"), true);
    // $latitude  = $input['latitude'] ?? null;
    // $longitude = $input['longitude'] ?? null;

    // Store location in session for later use
    $latitude = $_SESSION['latitude'] ?? 0.434342;
    $longitude  = $_SESSION['longitude'] ?? 6.54532;

    // Step 1: Ensure group_id is set
    if (!isset($_SESSION['group_id'])) {
        $sql = $conn->query("SELECT * FROM group_main
                             WHERE (group_rep_id = '$student_id' OR group_rep_id_2 = '$student_id') 
                             AND status = 'active'");
        if ($sql && mysqli_num_rows($sql) > 0) {   
            $row = mysqli_fetch_assoc($sql);
            $_SESSION['group_id'] = $row['group_id']; 
        } else {
            echo json_encode([
                "status" => "failed",
                "code"   => "Group Error Database not set",
                "code_b" => "Your group can't be found or is not updated. Contact infotess admin."
            ]);
            exit;
        }
    }
    $mygroup = $_SESSION['group_id'];

    // Step 2: Check if this group already has an active QR session
    $checkActive = $conn->query("SELECT * FROM qrcode 
                                 WHERE created_by = '$student_id' 
                                 AND is_active = '1' 
                                 AND group_id = '$mygroup'");

    if ($checkActive && mysqli_num_rows($checkActive) > 0) {
        $row = mysqli_fetch_assoc($checkActive);
        $_SESSION['qr_session'] = $row['session_code'];
        echo json_encode([
            "status"  => "success",
            "message" => "You already have an active session.",
            "code"    => $row['QRcode'],
            "code_b"  => $row['session_code'],
            "latitude"  => $_SESSION['latitude'] ?? null,
            "longitude" => $_SESSION['longitude'] ?? null
        ]);
        exit;
    }

    // Step 3: Generate new QR codes if no active session exists
    $code      = rand(0,5);
    $cc_QRCode = 'QRCodeForUEW101att';

    if ($code < 1) {
        $cc_QRCode .= rand(0,4000) . "he$^**is" . $code;
    } else if ($code <= 3) {
        $cc_QRCode .= rand($code,2000) . "h3!#e1i%2s$" . $code;
    } else {
        $cc_QRCode .= rand($code,2030) . "^&*sd%gh%h3!#e1i%2s$" . $code;
    }

    $sessionQr = rand(0,10000) . "UEW" . $code . "QR" . rand(-9000 , 1223947) ."heis";

    //  Correct INSERT with matching columns and placeholders
    $stmt = $conn->prepare("INSERT INTO qrcode 
        (QRcode, session_code, longitude, latitude, course_id, group_id, is_active, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $isActive = 1;
    $stmt->bind_param(
        "ssssssss",
        $cc_QRCode,
        $sessionQr,
        $_SESSION['longitude'],
        $_SESSION['latitude'],
        $currentCourse,
        $mygroup,
        $isActive,
        $student_id
    );

    $result_sql = $stmt->execute();
    $_SESSION['qr_session'] = $sessionQr;

    if ($result_sql) {
        echo json_encode([
            "status"    => "success",
            "code"      => $cc_QRCode,
            "code_b"    => $sessionQr,
            "latitude"  => $_SESSION['latitude'],
            "longitude" => $_SESSION['longitude']
        ]);
    } else {
        echo json_encode([
            "status"  => "failed",
            "message" => "Database insertion error"
        ]);
    }
} else {
    echo json_encode([
        "status"  => "failed",
        "message" => "POST request only"
    ]);
}
?>
