# Lab: SQL injection with filter bypass via XML encoding

**PRACTITIONER**

This lab contains a SQL injection vulnerability in its stock check feature. The results from the query are returned in the application's response, so you can use a UNION attack to retrieve data from other tables.

The database contains a users table, which contains the usernames and passwords of registered users. To solve the lab, perform a SQL injection attack to retrieve the admin user's credentials, then log in to their account.

## Write-up

Lab này injection nằm ở request check stock dạng XML, và có filter chặn ký tự đặc biệt.

Em chặn request POST /product/stock rồi sửa giá trị trong thẻ XML để chèn UNION payload.

Payload chính vẫn như các lab UNION:

1 UNION SELECT username || '~' || password FROM users

Do filter chặn ký tự, em encode payload sang XML entities rồi gửi lại request.
Khi bypass filter thành công, response sẽ trả về username/password