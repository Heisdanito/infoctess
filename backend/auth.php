<?php

session_start();

require_once '.././backend/connection/connection.php';

// Capture any PHP errors/notices to return as JSON
ob_start();

$email = $_POST['email'] ?? 'i suspended this';
$psw  = $_POST['psw'] ?? ''; 
$Token = $_POST['key'] ?? '';

// Default index if null
$index = 5262140032;

if(empty($Token)){
    http_response_code(400);
    echo json_encode([
        "status" => "Error key Stu",
        "message" => "System error please try again..."
    ]);
    exit;
}

$email = $conn->real_escape_string($email); 
$psw = $conn->real_escape_string($psw);

if(empty($email) && empty($psw)){
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Please Enter your Student Id and Student email"
    ]);
    exit;
} else {
    $sql = "SELECT student_id , student_mail , programme FROM students WHERE student_id = '$psw'  ";
    $result = $conn->query($sql);
    
    //check for valid credentials
    if($result && mysqli_num_rows($result) > 0 ){ 
        //get token from 
        require_once '.././backend/token/activeToken.php';

        $_SESSION['token'] = $severToken ?? $index;
        //build redirection 
        http_response_code(200);
        echo json_encode([
            "status" => "JWTsuccess",
            "message" => "call for redirection",
            "nextPage" => "../backend/token/verifyActivetoken.php"
        ]);              
    } else {
        http_response_code(401);
        echo json_encode([
            "status" => "Login failed",
            "message" => "Invalid Credentials."
        ]);        
    }
}

ob_end_flush();
?>