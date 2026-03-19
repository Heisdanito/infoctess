<?php
session_start();
header("Content-Type: application/json");
require_once '../connection/connection.php';

$query = "SELECT programme_code, programme_name FROM programme ORDER BY programme_code";
$result = mysqli_query($conn, $query);

$programmes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $programmes[] = [
        'programme_code' => $row['programme_code'],
        'programme_name' => $row['programme_name']
    ];
}

echo json_encode([
    'status' => 'success',
    'data' => $programmes
]);

mysqli_close($conn);
?>