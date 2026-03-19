<?php

session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '.././backend/connection/connection.php';


    $email = $_POST['email'] ?? '';
    $psw  = $_POST['psw'] ?? '';
    $Token = $_POST['key'] . $psw ?? '';

    if(empty($Token)){
        echo json_encode([
            "status" => "Error key Stu",
            "message" => "System error please try again..."
        ]);
    }

     $email = $conn->real_escape_string($email); 
     $psw = $conn->real_escape_string($psw);

    if(empty($email) && empty($psw)){
        echo json_encode([
            "status" => "error",
            "message" => "Please Enter your Student Id and Student email"
        ]);
    }else{
        $sql = "SELECT student_id , student_mail , programme FROM students WHERE student_id = '$psw' AND student_mail = '$email' ";
        $result = $conn->query($sql);
        //check for  valid credentials
        if(mysqli_num_rows($result) > 0 ){
            //get token from 

            require_once '.././backend/token/activeToken.php';

                $_SESSION['token'] = $severToken;
                //build redirection 
                echo json_encode([
                    "status" => "JWTsuccess",
                    "message" => "call for redirection",
                    "nextPage" => "../backend/token/verifyActivetoken.php"
                ]);              
        }else{
            echo json_encode([
                "status" => "Login failed",
                "message" => "Invalid Credentials."
            ]);        
        }
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Only  requests are allowed."
    ]);
}
?>
