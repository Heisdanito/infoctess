<?php
$db_host = getenv('DB_HOST') ?: "mysql-291ab10a-heisdanito-7ee7.b.aivencloud.com";
$db_user = getenv('DB_USER') ?: "avnadmin";
$db_psw  = getenv('DB_PASS') ?: "";
$db_name = getenv('DB_NAME') ?: "defaultdb";
$port    = (int)(getenv('DB_PORT') ?: 21225);



DB_HOST = mysql-291ab10a-heisdanito-7ee7.b.aivencloud.com
DB_USER = avnadmin
DB_PASS = AVNS_ZFYiFvpqdF-G5jN0vXu
DB_NAME = defaultdb
DB_PORT = 21225
```

### Step 3 — Allow the secret on GitHub (quickest fix)
Click the link GitHub gave you:
```
//https://github.com/Heisdanito/infoctess/security/secret-scanning/unblock-secret/3BHiHuVxg...

try {
    // SSL must be configured BEFORE connecting
    $conn = new mysqli();
    $conn->ssl_set(NULL, NULL, __DIR__ . '/ca.pem', NULL, NULL);
    $conn->real_connect($db_host, $db_user, $db_psw, $db_name, $port, NULL, MYSQLI_CLIENT_SSL);

    if ($conn->connect_error) {
        echo json_encode([
            "status"  => "error",
            "message" => "Connection failed: " . $conn->connect_error
        ]);
        exit;
    }

} catch (mysqli_sql_exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
?>
