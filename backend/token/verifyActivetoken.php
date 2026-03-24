<?php
session_start();
require_once '../../backend/connection/connection.php';
if(!isset($_SESSION['token'])){
    //redirection 
    echo "
        <script>
            window.location.href = '../../auth/login.html'
        </script>
    ";
}else{
    $severToken = $_SESSION['token'];
    $stmt = $conn->query("SELECT severToken , is_active , student_id FROM tokens WHERE severToken = '$severToken' ");
    if(mysqli_num_rows($stmt) > 0 ){
        while($row = mysqli_fetch_assoc($stmt)){
            $student_id = $row['student_id'];

            $_SESSION['student_id'] = $student_id;

            $sql = $conn->query("SELECT student_name, student_id, roles  FROM students WHERE student_id = '$student_id' ");
            if(mysqli_num_rows($sql) > 0){
                while($row = mysqli_fetch_assoc($sql)){
                    //$student_name = $row['student_id'];
                    $_SESSION['student_name'] = $row['student_name'];
                    $_SESSION['role'] = $row['roles'];

                    //condition based on the page to send admin and students to UEW 
                    if($row['roles'] === 'rep' || $row['roles'] === 'ta' || $row['roles'] === 'lec'){
                            //redirection
                            echo "done"; 
                            echo "
                            <script>
                                window.location.href = '../../app/location.php'
                            </script>
                        ";
                    }else{
                            //redirection 
                            echo "
                            <script>
                                window.location.href = '../../app/user.html'
                            </script>
                        ";

                        echo "done"; 
                    } 
                }
            }else{

                echo "
                    <script>
                        window.location.href = '../auth/404.html'
                    </script>
                ";

            }
        }
    }else{
        echo 'nodata found';
    }
}
