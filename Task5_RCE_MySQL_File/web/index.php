<?php
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_password = getenv("DB_PASSWORD") ?: "password";
$db_name = getenv("DB_NAME") ?: "mysql_file_priv_db";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) die("Connection failed");

$result_message = "";
$user_data = null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_id"])) {
    $user_id = $_POST["user_id"];
    
    // VULNERABLE
    $query = "SELECT id, username, bio, file_priv_status FROM users WHERE id = $user_id";
    
    // Support multi query for this lab
    if ($mysqli->multi_query($query)) {
        do {
            if ($result = $mysqli->store_result()) {
                $user_data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                $result_message = "Query executed successfully.";
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    } else {
        $result_message = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Task 5 - MySQL into_outfile RCE</h2>
    <form method="POST">
        <input type="text" name="user_id" placeholder="User ID">
        <button type="submit">Search</button>
    </form>
    <p><?= htmlspecialchars($result_message) ?></p>
    <?php if ($user_data) echo "<pre>".print_r($user_data, true)."</pre>"; ?>
</body>
</html>
