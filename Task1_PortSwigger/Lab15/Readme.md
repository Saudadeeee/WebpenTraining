# Lab: Blind SQL injection with time delays and information retrieval

**PRACTITIONER**

This lab contains a blind SQL injection vulnerability. The application uses a tracking cookie for analytics, and performs a SQL query containing the value of the submitted cookie.

The results of the SQL query are not returned, and the application does not respond any differently based on whether the query returns any rows or causes an error. However, since the query is executed synchronously, it is possible to trigger conditional time delays to infer information.

The database contains a different table called users, with columns called username and password. You need to exploit the blind SQL injection vulnerability to find out the password of the administrator user.

To solve the lab, log in as the administrator user.

## Write-up

Lab này giống lab time delay, nhưng phải lấy được password của administrator.

Đầu tiên em test điều kiện delay:

TrackingId=xyz'||(SELECT CASE WHEN (1=1) THEN pg_sleep(10) ELSE pg_sleep(0) END)--

Sau đó đổi điều kiện để dò độ dài password, ví dụ LENGTH(password)>n cho user administrator. Request nào delay thì điều kiện đúng.

Khi biết độ dài, em dò từng ký tự bằng SUBSTRING(password,pos,1) với tập a-z0-9. Nếu ký tự đoán đúng thì request sẽ delay 10 giây.

Ghép đủ từng vị trí sẽ ra password đầy đủ, rồi dùng password đó login administrator.

Lab này bản chất là boolean-based blind nhưng kênh side-channel là thời gian.
