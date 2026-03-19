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




                        $mygroup = $_SESSION['group_id'];
                    
                            // Already has active session → return it
                         echo json_encode([
                                "status" => "fetched",
                                "username" => "$student_name",
                                "lastlogin" => "$last_login",
                                "stu_id" => "$student_id",
                                "group_id" => "$mygroup",
                            ]);
                        
                        
                        
                        //     $conn->query( 
                        //  "UPDATE students
                        //         SET updated_at = NOW()
                        //         WHERE student_id = '$student_id' "
                        //     );
                        // }else{
                        //     echo json_encode([
                        //         "status" => "fetched",
                        //         "username" => "$student_name",
                        //         "lastlogin" => "$last_login",
                        //         "stu_id" => "$student_id",
                        //         "group_id" => "$mygroup",
                        //     ]);
                        // }

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