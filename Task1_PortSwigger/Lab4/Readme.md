# Lab: SQL injection attack, querying the database type and version on MySQL and Microsoft

**PRACTITIONER**

This lab contains a SQL injection vulnerability in the product category filter. You can use a UNION attack to retrieve the results from an injected query.

To solve the lab, display the database version string.

## Write-up

Tương tự level trên , lab này yêu cầu xác định được db version nhưng của mysql và mssql

payload sử dụng:' UNION SELECT @@version,NULL --