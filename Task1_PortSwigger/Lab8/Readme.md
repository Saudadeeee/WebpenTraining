# Lab: SQL injection UNION attack, finding a column containing text

**PRACTITIONER**

This lab contains a SQL injection vulnerability in the product category filter. The results from the query are returned in the application's response, so you can use a UNION attack to retrieve data from other tables. To construct such an attack, you first need to determine the number of columns returned by the query. You can do this using a technique you learned in a previous lab. The next step is to identify a column that is compatible with string data.

The lab will provide a random value that you need to make appear within the query results. To solve the lab, perform a SQL injection UNION attack that returns an additional row containing the value provided. This technique helps you determine which columns are compatible with string data.

## Write-up

Mục tiêu của lab này mở rộng hơn lab trên, tìm cột nào mà chứa text. Vẫn sử dụng phương pháp như trên với UNION em có thể có được payload như sau:
' UNION SELECT NULL,NULL-- khi có được số lượng cột đúng, thay thế từng phần tử NULL thành 'text' để xác định 