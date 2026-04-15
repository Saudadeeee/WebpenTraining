<?php
$db_host = getenv("DB_HOST") ?: "localhost";
$db_user = getenv("DB_USER") ?: "root";
$db_password = getenv("DB_PASSWORD") ?: "password";
$db_name = getenv("DB_NAME") ?: "rce_file_db";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) die("Connection failed");

$result_message = "";
$user_data = null;

$id = $_GET["id"] ?? ($_POST["id"] ?? null);

if ($id !== null && $id !== "") {
    // Intentionally vulnerable for lab: unsafely concatenated input.
    $query = "SELECT id, username, email FROM users WHERE id = '$id'";

    $result = $mysqli->query($query);
    if ($result === true) {
        // Non-SELECT payloads (e.g., INTO OUTFILE) return boolean true.
        $result_message = "Query executed successfully.";
    } elseif ($result instanceof mysqli_result) {
        $user_data = $result->fetch_all(MYSQLI_ASSOC);
        $result->free();
        $result_message = "Query executed successfully.";
    } else {
        $result_message = "Error: " . $mysqli->error;
    }
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Task 5 - MySQL into_outfile RCE</h2>
    <form method="GET">
        <input type="text" name="id" placeholder="id (example: 1)">
        <button type="submit">Search</button>
    </form>
    <p><?= htmlspecialchars($result_message) ?></p>
    <?php if ($user_data) echo "<pre>".print_r($user_data, true)."</pre>"; ?>
    <p>Uploads path: /uploads/</p>
</body>
</html>
