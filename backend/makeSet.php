<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '.././backend/connection/connection.php';

    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true); // Decode JSON to PHP associative array
    

    $currentCourse = $data['name'] ?? '';
    $_SESSION['Activecourse'] = $currentCourse ?? '';
    echo json_encode([
        "status" => "success",
        "message" => "All set Session started waiting to create",
        "data" => "$currentCourse"
    ]);
}else{
    echo json_encode([
        "status" => "failed",
        "message" => "System error please try again..."
    ]);
}
