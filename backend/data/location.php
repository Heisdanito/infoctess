<?php
session_start();
header("Content-Type: application/json");

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

$latitude = $data['latitude'] ?? null;
$longitude = $data['longitude'] ?? null;

if ($latitude && $longitude) {
    $_SESSION['latitude'] = $latitude;
    $_SESSION['longitude'] = $longitude;
  ;

    echo json_encode([
        "status" => "success",
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);

} else {
    echo json_encode([
        "status" => "failed",
        "message" => "Latitude/Longitude missing"
    ]);

    
}
?>
