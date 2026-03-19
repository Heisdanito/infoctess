<?php
header(header: "Content-type: text/javascript");
session_start();
require_once '../connection/connection.php';
    if(isset($_SESSION['token'])){
        $severToken = $_SESSION['token'];
        $stmt = $conn->query("SELECT severToken , is_active , student_id FROM tokens WHERE severToken = '$severToken' AND is_active = '1' ");
        if(mysqli_num_rows($stmt) > 0 ){
            while($row = mysqli_fetch_assoc($stmt)){
                $student_id = $row['student_id'];
                $_SESSION['student_id'] = $student_id;
                
    
                $sql = $conn->query("SELECT student_name, student_id, roles ,updated_at ,group_id  FROM students WHERE student_id = '$student_id' ");
                if(mysqli_num_rows($sql) > 0){
                    while($row = mysqli_fetch_assoc($sql)){
                        //$student_name = $row['student_id'];
                        $_SESSION['student_name'] = $row['student_name'];
                        $_SESSION['role'] = $row['roles'];
                        $last_login  = $row['updated_at'];
                        $student_name = $_SESSION['student_name'];
                        $_SESSION['group_id'] = $row['group_id'];
                        $mygroup = $_SESSION['group_id'];

                        
                        $conn->query( 
                         "UPDATE students
                                SET updated_at = NOW()
                                WHERE student_id = '$student_id' "
                            );


                        $mygroup = $_SESSION['group_id'];

                        $created_at = new DateTime($row['updated_at']);
                        $now = new DateTime();
                        $diff = $now->diff($created_at);
                    
                        if ($diff->y > 0) {
                            $timeAgo = $diff->y . " year" . ($diff->y > 1 ? "s" : "") . " ago";
                        } elseif ($diff->m > 0) {
                            $timeAgo = $diff->m . " month" . ($diff->m > 1 ? "s" : "") . " ago";
                        } elseif ($diff->d > 0) {
                            $timeAgo = $diff->d . " day" . ($diff->d > 1 ? "s" : "") . " ago";
                        } elseif ($diff->h > 0) {
                            $timeAgo = $diff->h . " hour" . ($diff->h > 1 ? "s" : "") . " ago";
                        } elseif ($diff->i > 0) {
                            $timeAgo = $diff->i . " minute" . ($diff->i > 1 ? "s" : "") . " ago";
                        } else {
                            $timeAgo = "just now";
                        }
                    

                        // Step 2: Check if this group already has an active QR session
                        $checkActive = $conn->query("SELECT * FROM qrcode 
                                                     WHERE created_by = '$student_id' 
                                                     AND is_active = 1 
                                                     AND group_id = '$mygroup'");
                    
                        if ($checkActive && mysqli_num_rows($checkActive) > 0) {
                            // Already has active session → return it
                            $row = mysqli_fetch_assoc($checkActive);
                            $_SESSION['qr_session'] = $row['session_code'];

                            
                            
                            echo json_encode([
                                "status" => "fetched",
                                "username" => "$student_name",
                                "lastlogin" => "$timeAgo",
                                "stu_id" => "$student_id",
                                "group_id" => "$mygroup",
                                "code" => $row['QRcode'],
                                "code_b" => $row['session_code']
                            ]);
                        
                        }else{
                            echo json_encode([
                                "status" => "fetched",
                                "username" => "$student_name",
                                "lastlogin" => "$timeAgo",
                                "stu_id" => "$student_id",
                                "group_id" => "$mygroup",
                                "code_b" => "none"

                            ]);
                        }

                    }


                
                }else{
                    echo "error";
                }
            }
        }else{
            echo json_encode([
                "status" => "Not fetchfetched",
                "message" => "Server token error"
            ]); 
        }  
    }else{

        echo json_encode([
            "status" => "Notfetch",
            "message" => "try bad gateway don't act smart"
        ]); 


    }
?>