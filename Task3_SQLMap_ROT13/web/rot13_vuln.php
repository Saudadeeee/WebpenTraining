<?php
$db_host = getenv("DB_HOST") ?: "task3-db";
$db_user = getenv("DB_USER") ?: "root";
$db_password = getenv("DB_PASSWORD") ?: "password";
$db_name = getenv("DB_NAME") ?: "rot13_sqli_db";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) die("Connection failed");

$result_message = "";
$user_data = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
    $encoded_email = $_POST["email"];
    $decoded_email = str_rot13($encoded_email);
    
    $query = "SELECT * FROM users WHERE email = '$decoded_email'";
    $result = $mysqli->query($query);
    
    if ($result) {
        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $result_message = "Found!";
        } else {
            $result_message = "Not found!";
        }
    } else {
        $result_message = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Task 3 - ROT13 SQLi</h2>
    <form method="POST">
        <input type="text" name="email" placeholder="Email with ROT13 encoding">
        <button type="submit">Search</button>
    </form>
    <p><?= htmlspecialchars($result_message) ?></p>
    <?php if ($user_data) echo "<pre>".print_r($user_data, true)."</pre>"; ?>
</body>
</html>
