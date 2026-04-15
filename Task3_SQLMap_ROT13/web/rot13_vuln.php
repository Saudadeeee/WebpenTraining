<?php
$db_host = getenv("DB_HOST") ?: "task3-db";
$db_user = getenv("DB_USER") ?: "root";
$db_password = getenv("DB_PASSWORD") ?: "password";
$db_name = getenv("DB_NAME") ?: "rot13_sqli_db";

$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
if ($mysqli->connect_error) die("Connection failed");

$result_message = "";
$row_data = null;
$query_debug = "";

if (isset($_GET["id"])) {
    $encoded_id = $_GET["id"];
    $decoded_id = str_rot13($encoded_id);

    $query = "SELECT id, name, description, price, stock FROM products WHERE id = '$decoded_id'";
    $query_debug = $query;
    $result = $mysqli->query($query);

    if ($result) {
        if ($result->num_rows > 0) {
            $row_data = $result->fetch_assoc();
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
    <p>Target endpoint: <code>/?id=&lt;rot13_payload&gt;</code></p>
    <form method="GET">
        <input type="text" name="id" placeholder="id with ROT13 encoding" value="<?= isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '' ?>">
        <button type="submit">Search</button>
    </form>
    <p><?= htmlspecialchars($result_message) ?></p>
    <?php if ($query_debug) echo "<p><strong>Executed query:</strong> <code>" . htmlspecialchars($query_debug) . "</code></p>"; ?>
    <?php if ($row_data) echo "<pre>".print_r($row_data, true)."</pre>"; ?>
</body>
</html>
