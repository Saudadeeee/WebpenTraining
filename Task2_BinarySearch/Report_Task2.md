# Báo Cáo Task 2 - Time-based Blind SQL Injection

## 1. Phân Tích Lỗ Hổng

**Mục tiêu**: `http://localhost:8002/`

**Loại Lỗ Hổng**: Time-based Blind SQL Injection 
**Nguyên nhân**: Payload truyền vào tham số POST `search_id` không được lọc và nối thẳng vào chuỗi câu SQL. Mã nguồn không in ra kết quả hay báo lỗi, nhưng chúng ta có thể dựa vào độ trễ thời gian trả về của HTTP Request nhờ vào hàn `SLEEP()` của MySQL.
**Payload tiêu chuẩn**:
```sql
1' AND IF((ĐIỀU_KIỆN_ĐÚNG), SLEEP(3), 0) -- -
```
Nếu `ĐIỀU_KIỆN_ĐÚNG` xảy ra, trang web sẽ tải chậm hơn 3 giây. Nếu sai, web sẽ phản hồi ngay lập tức. 
Sử dụng tìm kiếm nhị phân giúp suy ra chính xác 1 ký tự chỉ trong trung bình 7 request

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
