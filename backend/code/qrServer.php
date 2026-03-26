<?php
header("Content-Type: application/json");
session_start();
require_once '../../backend/connection/connection.php';

// Ensure database connection exists
if (!$conn) {
    echo json_encode([
        "status" => "failed",
        "message" => "Database connection error"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== '') {
    $student_id    = $_SESSION['student_id'] ?? null;
    $currentCourse = $_SESSION['Activecourse'] ?? null; // fallback course code

    // Location defaults
    $latitude  = $_SESSION['latitude']  ?? 5.323444;//null;
    $longitude = $_SESSION['longitude'] ?? 0.323444 ;//null;

    // Step 1: Ensure group_id is set
    if (!isset($_SESSION['group_id'])) {
        $stmt = $conn->prepare("SELECT group_id FROM `groups`
                                WHERE (group_rep_id = ? OR group_rep_id_2 = ?) 
                                AND status = 'active'");
        $stmt->bind_param("ss", $student_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
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
    $stmt = $conn->prepare("SELECT QRcode, session_code FROM qrcode 
                            WHERE created_by = ? AND is_active = 1 AND group_id = ?");
    $stmt->bind_param("ss", $student_id, $mygroup);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['qr_session'] = $row['session_code'];
        echo json_encode([
            "status"    => "success",
            "message"   => "You already have an active session.",
            "code"      => $row['QRcode'],
            "code_b"    => $row['session_code'],
            "latitude"  => $latitude,
            "longitude" => $longitude
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

    // Insert new QR session
    $stmt = $conn->prepare("INSERT INTO qrcode 
        (QRcode, session_code, longitude, latitude, course_id, group_id, is_active, created_by, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $isActive = 1;
    $stmt->bind_param(
        "ssssssis",
        $cc_QRCode,
        $sessionQr,
        $longitude,
        $latitude,
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
            "latitude"  => $latitude,
            "longitude" => $longitude
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
