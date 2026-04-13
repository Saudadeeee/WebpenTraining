<?php
try {
    $db_host = getenv("DB_HOST") ?: "task4-case1-mssql";
    $conn = new PDO("dblib:host=$db_host;dbname=master", "SA", "StrongP@ssw0rd!");
    
    $msg = "";
    $data = null;
    
    if ($_POST["product_id"] ?? null) {
        $id = $_POST["product_id"];
        try {
            $data = $conn->query("SELECT * FROM products WHERE id = " . $id)->fetchAll(PDO::ASSOC);
            $msg = count($data) > 0 ? "Found" : "Not found";
        } catch(Exception $e) {
            $msg = "Error: " . $e->getMessage();
        }
    }
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<body>
    <h2>Task 4 Case 1 - Same Server</h2>
    <form method="POST">
        <input type="text" name="product_id" placeholder="Product ID">
        <button>Search</button>
    </form>
    <p><?= htmlspecialchars($msg) ?></p>
    <?php if ($data) echo "<pre>".print_r($data, true)."</pre>"; ?>
</body>
</html>
