# Lab: SQL injection UNION attack, determining the number of columns returned by the query

**PRACTITIONER**

This lab contains a SQL injection vulnerability in the product category filter. The results from the query are returned in the application's response, so you can use a UNION attack to retrieve data from other tables. The first step of such an attack is to determine the number of columns that are being returned by the query. You will then use this technique in subsequent labs to construct the full attack.

To solve the lab, determine the number of columns returned by the query by performing a SQL injection UNION attack that returns an additional row containing null values.

## Write-up

Ở lab này chỉ yêu cầu về xác định xem số lượng cột có trong bảng sử dụng UNION attack
Payload em sử dụng: '+UNION+SELECT+NULL,NULL-- thử với các số lượng NULL khác nhau để xác định được số lượng và đáp án ở đây là 3
