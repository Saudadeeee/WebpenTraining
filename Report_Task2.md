# Báo Cáo Task 2 - Time-based Blind SQL Injection

## 1. Phân Tích Lỗ Hổng

**Mục tiêu**: `http://localhost:8002/`

**Loại Lỗ Hổng**: Time-based Blind SQL Injection (Binary Search).
**Nguyên nhân**: Payload truyền vào tham số POST `search_id` không được lọc và nối thẳng vào chuỗi câu SQL. Mã nguồn không in ra kết quả hay báo lỗi, nhưng chúng ta có thể dựa vào độ trễ thời gian trả về của HTTP Request nhờ vào hàn `SLEEP()` của MySQL.
**Payload tiêu chuẩn**:
```sql
1' AND IF((ĐIỀU_KIỆN_ĐÚNG), SLEEP(1), 0) -- -
```
Nếu `ĐIỀU_KIỆN_ĐÚNG` xảy ra, trang web sẽ tải chậm hơn 1 giây. Nếu sai, web sẽ phản hồi ngay lập tức. Đây là cơ sở cốt lõi để chúng ta "hỏi" cơ sở dữ liệu.

## 2. Các Bước Khai Thác Kém Hiệu Quả vs Tối Ưu (Binary Search)
Thay vì sử dụng Bruteforce tuyến tính (Linear Search - phải thử từng ký tự trong khoảng ASCII 0-255 với chi phí lên đến 255 request / 1 ký tự), ta sử dụng thuật toán **Binary Search (Tìm Kiếm Nhị Phân)**:
Thuật toán này liên tục chia nhỏ không gian mẫu ra làm 2 nửa. Với mỗi khoảng [low, high], kịch bản hỏi xem ký tự đó có lớn hơn giá trị `mid` hay không thông qua truy vấn `ASCII(SUBSTRING(...)) > mid`. 
Cách này giúp suy ra chính xác 1 ký tự chỉ trong trung bình **~7 request** (giảm thiểu tới ~95% lượng gói tin và thời gian thực thi).

## 3. Khai Thác Bằng Script Tự Động

Mã khai thác bằng Python (không cần thư viện bên thứ ba `requests`, tích hợp sẵn `urllib`):
`Task2_BinarySearch/scripts/exploit_time_based.py`

### 3.1 Giao Tiếp Tìm Kiếm Độ Dài Của Chuỗi
Đầu tiên, kiểm tra độ dài của password (secret) cột của user id 1:
```sql
1' AND IF(LENGTH((SELECT secret FROM users WHERE id=1)) > 15, SLEEP(1), 0) -- -
```

### 3.2 Khai Thác Tìm Kiếm Ký Tự
Sau khi xác định được độ dài (30 ký tự), ta lần lượt tiến hành binary search cho từng ký tự:
```sql
1' AND IF(ASCII(SUBSTRING((SELECT secret FROM users WHERE id=1), 1, 1)) > 64, SLEEP(1.0), 0) -- -
```

## 4. Kết Quả Thực Tế (Execution Output)

Khi chạy script do thám vào Docker container mục tiêu:
```text
PS D:\> python Task2_BinarySearch/scripts/exploit_time_based.py
[*] Bắt đầu script khai thác Time-based Blind SQLi...
[*] Đang tìm độ dài của chuỗi 'secret' (id=1)...
[+] Độ dài tìm được: 30 ký tự
[*] Đang trích xuất chuỗi secret (30 ký tự) bằng Binary Search (Time-based)...
[+] Đang giải mã: FLAG{blind_sqli_binary_search}
[+] Khai thác thành công! Kết quả cuối cùng: FLAG{blind_sqli_binary_search}
```

Kết quả đã tìm thấy chính xác **`FLAG{blind_sqli_binary_search}`** mà không cần lấy bất kỳ dữ liệu trực quan nào từ trang web ngoài việc đo đặc hiệu thời gian!