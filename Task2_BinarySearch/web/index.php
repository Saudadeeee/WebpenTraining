<?php
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_password = getenv("DB_PASSWORD") ?: "password";
$db_name = getenv("DB_NAME") ?: "blind_sqli_db";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) die("Connection failed");

$execution_time_msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["search_id"])) {
    $search_id = $_POST["search_id"];
    $query = "SELECT * FROM users WHERE id = '$search_id'";
    
    // Bắt đầu đo thời gian chạy truy vấn
    $start_time = microtime(true);
    
    $result = $mysqli->query($query);
    
    // Kết thúc đo thời gian
    $end_time = microtime(true);
    
    $time_taken = round($end_time - $start_time, 4);
    $execution_time_msg = "Thời gian thực thi: " . $time_taken . " giây";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Task 2 - Time-based Blind SQLi</title>
</head>
<body>
    <h2>Task 2 - Time-based Blind SQLi</h2>
    <form method="POST">
        <input type="text" name="search_id" placeholder="Payload here (e.g. 1' AND SLEEP(3) -- -)" size="60">
        <button type="submit">Search</button>
    </form>
    <br>
    <?php if ($execution_time_msg !== "") echo "<b>" . htmlspecialchars($execution_time_msg) . "</b>"; ?>
</body>
</html>
