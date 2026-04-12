<?php
$db_host = getenv("DB_HOST") ?: "task4-case2-mssql";
$db_user = getenv("DB_USER") ?: "SA";
$db_password = getenv("DB_PASSWORD") ?: "StrongP@ssw0rd!";
$db_name = getenv("DB_NAME") ?: "master";

$result_message = "";
$data = null;

try {
    $conn = new PDO("dblib:host=$db_host;dbname=$db_name", $db_user, $db_password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["product_id"])) {
        $product_id = $_POST["product_id"];
        $query = "SELECT * FROM products WHERE id = " . $product_id;
        
        try {
            $stmt = $conn->query($query);
            $data = $stmt->fetchAll(PDO::ASSOC);
            if(count($data) > 0) {
                $result_message = "Products found.";
            } else {
                $result_message = "No product found.";
            }
        } catch(PDOException $e) {
            $result_message = "Error: " . $e->getMessage();
        }
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Task 4 Case 2 - MSSQL RCE (Separate Server)</h2>
    <form method="POST">
        <input type="text" name="product_id" placeholder="Product ID">
        <button type="submit">Search</button>
    </form>
    <p><?= htmlspecialchars($result_message) ?></p>
    <?php if ($data) echo "<pre>".print_r($data, true)."</pre>"; ?>
</body>
</html>
