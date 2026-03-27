<?php
header("Content-Type: application/json");
session_start();

// ── Inline connection ──────────────────────────────────────────────────────
$db_host = "mysql-291ab10a-heisdanito-7ee7.b.aivencloud.com";
$db_user = "avnadmin";
$db_psw  = "AVNS_ZFYiFvpqdF-G5jN0vXu";
$db_name = "defaultdb";
$port    = 21225;
$ca_path = './ca.pem';

if (!file_exists($ca_path)) {
    echo json_encode(["status" => "failed", "message" => "ca.pem not found at: $ca_path"]);
    exit;
}
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli();
    $conn->ssl_set(NULL, NULL, $ca_path, NULL, NULL);
    $conn->real_connect($db_host, $db_user, $db_psw, $db_name, $port, NULL, MYSQLI_CLIENT_SSL);
} catch (Exception $e) {
    echo json_encode(["status" => "failed", "message" => "DB connect failed: " . $e->getMessage()]);
    exit;
}

// ── Session check ──────────────────────────────────────────────────────────
$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    echo json_encode(["status" => "failed", "message" => "Student ID not set in session"]);
    exit;
}

$currentCourse = $_SESSION['Activecourse'] ?? null;
$latitude      = $_SESSION['latitude']     ?? 5.323444;
$longitude     = $_SESSION['longitude']    ?? 0.323444;

// ── Step 1 — Ensure group_id is set ───────────────────────────────────────
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
            "code"   => "Group Error",
            "code_b" => "Your group can't be found or is not updated. Contact infotess admin."
        ]);
        exit;
    }
}

$mygroup = $_SESSION['group_id'];

// ── Step 2 — Check for existing active session ────────────────────────────
$stmt = $conn->prepare("SELECT QRcode, session_code FROM qrcode WHERE is_active = 1 AND group_id = ?");
$stmt->bind_param("s", $mygroup);
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

// ── Step 3 — Generate new QR code ─────────────────────────────────────────
$code      = rand(0, 5);
$cc_QRCode = 'QRCodeForUEW101att';

if ($code < 1) {
    $cc_QRCode .= rand(0, 4000) . "he$^**is" . $code;
} elseif ($code <= 3) {
    $cc_QRCode .= rand($code, 2000) . "h3!#e1i%2s$" . $code;
} else {
    $cc_QRCode .= rand($code, 2030) . "^&*sd%gh%h3!#e1i%2s$" . $code;
}

$sessionQr    = rand(0, 10000) . "UEW" . $code . "QR" . rand(-9000, 1223947) . "heis";
$isActive     = 1;
$serialStatus = 'qrcode';
$expire_at    = date("Y-m-d H:i:s", strtotime("+2 hours"));

// ── Step 4 — Insert new session ───────────────────────────────────────────
$stmt = $conn->prepare("INSERT INTO qrcode 
    (QRcode, session_code, longitude, latitude, course_id, group_id, is_active, created_by, serial_status, expire_at, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

// 10 params: s s d d s s i s s s
$stmt->bind_param(
    "ssddssisss",
    $cc_QRCode,
    $sessionQr,
    $longitude,
    $latitude,
    $currentCourse,
    $mygroup,
    $isActive,
    $student_id,
    $serialStatus,
    $expire_at
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
        "message" => "Database insertion error: " . $stmt->error
    ]);
}

$conn->close();
?>
