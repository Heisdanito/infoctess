<?php
if(isset($Token)){
    //variables
    $isActive = 1;

    $severToken = rand(0 ,2000);
    $severToken = $severToken . $psw . $Token . rand(0 , 1 );

    $stmt = $conn->prepare("INSERT INTO tokens (student_id , token , is_active, severToken, updated_at, created_at)
                    VALUES(?, ?, ?, ?, NOW(), NOW())
        ");
    $stmt->bind_param("ssss" , $psw , $Token , $isActive , $severToken);
    $result = $stmt->execute();

    if($result){

        $_SESSION['token'] = $severToken;

    }


}