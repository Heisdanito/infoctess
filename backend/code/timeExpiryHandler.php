<?php
header("Content-Type: application/json");
session_start();

// ── Inline connection ──────────────────────────────────────────────────────
$db_host = "mysql-291ab10a-heisdanito-7ee7.b.aivencloud.com";
$db_user = "avnadmin";
$db_psw  = "AVNS_ZFYiFvpqdF-G5jN0vXu";
$db_name = "defaultdb";
$port    = 21225;
$ca_path = __DIR__ . '/../../ca.pem';

if (!file_exists($ca_path)) {
    echo json_encode(["status" => "error", "message" => "ca.pem not found"]);
    exit;
}
try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli();
    $conn->ssl_set(NULL, NULL, $ca_path, NULL, NULL);
    $conn->real_connect($db_host, $db_user, $db_psw, $db_name, $port, NULL, MYSQLI_CLIENT_SSL);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "DB connect failed: " . $e->getMessage()]);
    exit;
}

// ── Check active QR session ────────────────────────────────────────────────
$isActive = 1;
$stmt = $conn->prepare("SELECT created_at FROM qrcode WHERE is_active = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $isActive);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $createdAt       = $row['created_at'];
    $createdDateTime = new DateTime($createdAt);
    $currentDateTime = new DateTime("now");
    $interval        = $createdDateTime->diff($currentDateTime);
    $totalMinutes    = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    // Expire after 2 hours 10 minutes (130 minutes)
    if ($totalMinutes > 130) {
        $stmt->close();

        // Deactivate expired QR codes
        $deactivate  = 0;
        $activeCheck = 1;
        $stmt2 = $conn->prepare("UPDATE qrcode SET expire_at = NOW(), is_active = ? WHERE is_active = ?");
        $stmt2->bind_param("ii", $deactivate, $activeCheck);

        if ($stmt2->execute()) {
            echo json_encode([
                "status"  => "expired",
                "message" => "QR code expired and deactivated after 2hrs 10min"
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "Failed to deactivate QR code: " . $stmt2->error
            ]);
        }
        $stmt2->close();

    } else {
        $stmt->close();
        echo json_encode([
            "status"     => "success",
            "created_at" => $createdAt,
            "difference" => $interval->format("%a days, %h hours, %i minutes"),
            "minutes_used"    => $totalMinutes,
            "minutes_remaining" => 130 - $totalMinutes
        ]);
    }

} else {
    $stmt->close();
    echo json_encode(["status" => "error", "message" => "No active QR code found"]);
}

$conn->close();
?>
