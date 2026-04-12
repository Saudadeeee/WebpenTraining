# Lab: Blind SQL injection with time delays

**PRACTITIONER**

This lab contains a blind SQL injection vulnerability. The application uses a tracking cookie for analytics, and performs a SQL query containing the value of the submitted cookie.

The results of the SQL query are not returned, and the application does not respond any differently based on whether the query returns any rows or causes an error. However, since the query is executed synchronously, it is possible to trigger conditional time delays to infer information.

To solve the lab, exploit the SQL injection vulnerability to cause a 10 second delay.

## Write-up

Lab này không có tín hiệu trên giao diện, nên mình dùng thời gian phản hồi làm tín hiệu.

Em sửa TrackingId để gọi sleep, ví dụ:

TrackingId=xyz'||pg_sleep(10)--

Khi gửi request mà response delay khoảng 10 giây thì xác nhận được SQLi.

Mục tiêu lab chỉ cần tạo được delay 10s là xong, nên payload sleep chạy được là solve.

Lab này mấu chốt là đo thời gian response thay cho nội dung response.
