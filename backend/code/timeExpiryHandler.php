<?php
session_start();
// Check if user is logged in
require '../../backend/connection/connection.php';
$student_id = $_SESSION['student_id'] ?? '';
$s = 1;
// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT created_at FROM QRCode WHERE  is_active = ? ");
$stmt->bind_param("s", $s);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $createdAt = $row['created_at'];

    // Convert to DateTime objects
    $createdDateTime = new DateTime($createdAt);
    $currentDateTime = new DateTime("now");

    // Calculate difference
    $interval = $createdDateTime->diff($currentDateTime);

    // Convert difference to total minutes
    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    // Threshold: 2 hours 10 minutes = 130 minutes
    $thresholdMinutes = 130;
    $s = 1;
    if ($totalMinutes > $thresholdMinutes) {
        $sql = "UPDATE qrcode SET expire_at = NOW(), is_active = ? WHERE is_active = ?"; $stmt = $conn->prepare($sql); // Here is_active is an integer, student_id is a string 
            $isActive = 0; 
            $stmt->bind_param("is", $isActive, $s); 
            if ($stmt->execute()) { 
                // echo json_encode([ 
                //     "status" => "success",
                //     "message" => "QR code egenerated for 2hr "
                // ]);
            }

    } else {
        echo json_encode([
            "status" => "success",
            "created_at" => $createdAt,
            "difference" => $interval->format("%a days, %h hours, %i minutes")
        ]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No active QR code found"]);
}

$stmt->close();
$conn->close();
?>
