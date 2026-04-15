<?php
try {
    // Case 1: web and MSSQL share the same network namespace (localhost DB).
    $db_host = getenv("MSSQL_HOST") ?: "127.0.0.1";
    $db_user = getenv("MSSQL_USER") ?: "sa";
    $db_pass = getenv("MSSQL_PASSWORD") ?: "P@ssw0rd123!";
    $conn = new PDO("dblib:host=$db_host;dbname=master", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("IF DB_ID('rce_test') IS NULL CREATE DATABASE rce_test;");
    $conn->exec("IF OBJECT_ID('rce_test.dbo.products', 'U') IS NULL BEGIN USE rce_test; CREATE TABLE products (id INT PRIMARY KEY IDENTITY(1,1), product_name VARCHAR(100), price DECIMAL(10, 2), stock INT); INSERT INTO products (product_name, price, stock) VALUES ('Laptop', 999.99, 5), ('Mouse', 25.50, 20), ('Keyboard', 75.00, 15); END");
    $conn->exec("IF OBJECT_ID('rce_test.dbo.app_config', 'U') IS NULL BEGIN USE rce_test; CREATE TABLE app_config (config_key VARCHAR(100) PRIMARY KEY, config_value VARCHAR(255) NOT NULL); INSERT INTO app_config (config_key, config_value) VALUES ('diag_target', 'whoami'); END");

    $searchMsg = "";
    $searchData = [];
    $diagMsg = "";
    $diagOutput = "";
    $hostMsg = "";

    if (($_POST["action"] ?? "") === "search" && ($_POST["product_id"] ?? null)) {
        $id = $_POST["product_id"];
        try {
            // Intentionally vulnerable query for lab practice.
            $result = $conn->query("SELECT * FROM rce_test.dbo.products WHERE id = " . $id);
            if ($result instanceof PDOStatement) {
                $searchData = $result->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $searchData = [];
            }
            $searchMsg = count($searchData) > 0 ? "Search completed." : "No result.";
            if (stripos($id, ';') !== false) {
                $searchMsg = "Search completed (stacked query executed).";
            }
        } catch (Exception $e) {
            $searchMsg = "Search failed: " . $e->getMessage();
        }
    }

    if (($_POST["action"] ?? "") === "diag") {
        $targetRow = $conn->query("SELECT config_value FROM rce_test.dbo.app_config WHERE config_key = 'diag_target'")->fetch(PDO::FETCH_ASSOC);
        $diagTarget = $targetRow["config_value"] ?? "localhost";

        // RCE on web host, but command source is controlled via MSSQL stacked query.
        $diagOutput = shell_exec($diagTarget . " 2>&1");
        $diagMsg = "Maintenance command executed on web from MSSQL-stored value: " . $diagTarget;
    }

    if (($_POST["action"] ?? "") === "hostcheck") {
        $dbHost = (string)$conn->query("SELECT @@SERVERNAME AS s")->fetch(PDO::FETCH_ASSOC)['s'];
        $webHost = gethostname();
        $sameHint = ($db_host === "127.0.0.1" || strtolower($db_host) === "localhost") ? "likely SAME server (DB endpoint is localhost)" : "likely DIFFERENT server";
        $hostMsg = "Web host: " . $webHost . " | DB host: " . $dbHost . " | DB endpoint from web: " . $db_host . " => " . $sameHint;
    }
} catch(Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Admin - Case 1</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; margin: 0; }
        .wrap { max-width: 860px; margin: 32px auto; background: #fff; padding: 24px; border: 1px solid #ddd; border-radius: 8px; }
        h1 { margin-top: 0; font-size: 24px; }
        .card { border: 1px solid #e3e7ef; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
        .row { display: flex; gap: 8px; }
        input[type=text] { flex: 1; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; }
        button { padding: 8px 14px; border: 0; background: #1f6feb; color: #fff; border-radius: 4px; cursor: pointer; }
        .msg { color: #334155; font-size: 14px; margin: 8px 0; }
        pre { background: #0f172a; color: #e2e8f0; padding: 10px; border-radius: 6px; overflow: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; font-size: 14px; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>Store Admin Dashboard (Case 1)</h1>
        <p>Product lookup and internal diagnostics for store operations (MSSQL same-server simulation).</p>

        <div class="card">
            <h3>Product Lookup</h3>
            <p class="msg">SQLi lab target on product_id (MSSQL). Use stacked query to change app_config.diag_target.</p>
            <form method="POST">
                <input type="hidden" name="action" value="search">
                <div class="row">
                    <input type="text" name="product_id" placeholder="Enter product id">
                    <button type="submit">Search</button>
                </div>
            </form>
            <div class="msg"><?= htmlspecialchars($searchMsg) ?></div>
            <?php if (!empty($searchData)): ?>
            <table>
                <tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th></tr>
                <?php foreach ($searchData as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['product_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['price'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['stock'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Maintenance Command</h3>
            <p class="msg">Runs command stored in app_config.diag_target (value can be changed via MSSQL stacked SQLi).</p>
            <form method="POST">
                <input type="hidden" name="action" value="diag">
                <button type="submit">Run</button>
            </form>
            <div class="msg"><?= htmlspecialchars($diagMsg) ?></div>
            <?php if ($diagOutput !== ""): ?>
            <pre><?= htmlspecialchars($diagOutput) ?></pre>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Host Relation Check</h3>
            <p class="msg">Compare PHP container hostname and MSSQL @@SERVERNAME.</p>
            <form method="POST">
                <input type="hidden" name="action" value="hostcheck">
                <button type="submit">Check</button>
            </form>
            <div class="msg"><?= htmlspecialchars($hostMsg) ?></div>
        </div>
    </div>
</body>
</html>
