# Task 2: Report Khai Thác Blind SQLi - Binary Search

## 1. Phân Tích Lỗ Hổng
Dựa trên source code `index.php`, hệ thống nhận tham số `id` trực tiếp qua biến `$_GET['id']` mà không sử dụng Prepared Statement, dẫn đến lỗ hổng SQL Injection.
Sự khác biệt ở đây là: Backend xử lý câu lệnh SQL, nhưng không trả hiển thị kết quả (data) hoặc Error (Lỗi logic) ra giao diện. Nếu query trả về ít nhất 1 dòng dữ liệu (True), giao diện hiển thị: `User found`. Ngược lại (False), giao diện hiển thị `User not found`.
Điều này tạo ra lỗ hổng **Boolean-based Blind SQL Injection**.

## 2. Chiến Lược Khai Thác
Vì không thể lấy data trực tiếp, chúng ta chỉ có thể "hỏi" cơ sở dữ liệu các câu hỏi Yes/No (True/False).
Để trích xuất dữ liệu dung lượng lớn (bảng `users` có 50+ columns), việc brute-force tuần tự từng ký tự (ASCII 32-126) là không khả thi (tốn quá nhiều HTTP request).
**Giải pháp**: Sử dụng thuật toán **Binary Search** (Tìm kiếm nhị phân).
- So sánh ASCII code của 1 ký tự với giá trị giữa (VD: ở giữa 32 và 126 là 79). Nếu `> 79`, ta thu hẹp khoảng tìm kiếm xuống nửa trên (80 - 126), ngược lại là nửa dưới.
- Tối ưu hóa: Thay vì 94 request cho 1 ký tự, binary search chỉ tốn tối đa `log2(94) ≈ 7 request`.

## 3. Quá Trình Khai Thác Thực Tế & Cách Vượt Qua

**Bước 1: Xác định độ dài của chuỗi (Length Enumeration)**
Trước khi tìm nội dung, cần biết độ dài để biết điểm dừng.
```sql
Payload: 1' AND LENGTH((SELECT username FROM users LIMIT 1)) = 5 -- -
```
Nếu server trả về `User found`, ta biết độ dài là 5. Trong Binary Search, ta có thể dò độ dài bằng `>`, `<` thay vì `=`:
`1' AND LENGTH(...) > 5` -> True/False -> Thu hẹp khoảng cách.

**Bước 2: Trích xuất từng ký tự (Character Extraction via Binary Search)**
Sau khi biết độ dài là N, ta sẽ trích xuất từng ký tự ở vị trí i (1 tới N).
```sql
Payload: 1' AND ASCII(SUBSTRING((SELECT username FROM users LIMIT 1), 1, 1)) > 79 -- -
```
**Lý do tại sao Binary Search vượt qua được giới hạn thời gian (Bypass Time Limit):**
- Trong Brute Force thông thường: Để tìm chữ `z` (ASCII 122), ta phải thử `ASCII()=32`, `...=33`, ..., `...=122`. Tốn 90 requests.
- Trong Binary Search: Khoảng ASCII chữ là [32, 126] (từ space tới dấu ngã).
  - Lần 1: Có `> (32+126)/2 = 79` không? (Chữ z là 122 -> Có -> True). Khoảng mới: [80, 126]
  - Lần 2: Có `> 103` không? (122 > 103 -> True). Khoảng mới: [104, 126]
  - Lần 3: Có `> 115` không? (122 > 115 -> True). Khoảng mới: [116, 126]
  - Lần 4: Có `> 121` không? (122 > 121 -> True). Khoảng mới: [122, 126]
  - Lần 5: Có `> 124` không? (122 > 124 -> False). Khoảng mới: [122, 124]
  - Lần 6: Có `> 123` không? (122 > 123 -> False). Khoảng mới: [122, 123]
  - Lần 7: Có `> 122` không? (122 > 122 -> False). Khoảng mới: [122, 122]. Lập tức kết luận là 122 (chữ `z`).
  => Chỉ tốn đúng 7 requests thay vì 90.

**Kết quả thực tế khi chạy script `exploit_binary_search.py`**:
Khi chạy với port 8002, công cụ Python sử dụng thư viện `requests` để gửi các payload trên vào tham số `id`. Thay vì phải phân tích từng Response bằng mắt, script đánh giá chữ `User found` trong response text. Nếu có, `return True`, nếu không, `return False`. Script hoàn tất việc lấy được 50+ column của users chỉ trong vài chục giây thay vì vài giờ.

## 4. Cách Khắc Phục (Vá Lỗi)
- Sử dụng Prepared Statements (PDO / MySQLi prepare).
- Ràng buộc tham số truyền vào: Ép kiểu số nguyên (type casting) thông qua hàm `intval()` hoặc `is_numeric()`.
