<?php
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$latitude  = $data['latitude']  ?? null;
$longitude = $data['longitude'] ?? null;

if (!$latitude || !$longitude) {
    echo json_encode([
        "status"  => "failed",
        "message" => "latitude and longitude are required"
    ]);
    exit;
}

$_SESSION['latitude']  = $latitude;
$_SESSION['longitude'] = $longitude;

echo json_encode([
    "status"    => "success",
    "message"   => "Location saved",
    "latitude"  => $latitude,
    "longitude" => $longitude
]);
exit;
?>
