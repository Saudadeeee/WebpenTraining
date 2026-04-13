#!/usr/bin/env python3
import codecs

def test_rot13_payload():
    # Payload SQLi gốc mà SQLMap tạo
    payload = "1' OR '1'='1"
    
    # Step 1: SQLMap tạo payload
    print(f"Step 1 - SQLMap tạo payload gốc:")
    print(f"  Original: {payload}")
    
    # Step 2: Tamper script mã hóa ROT13
    encoded = codecs.encode(payload, "rot_13")
    print(f"\nStep 2 - Tamper script ROT13 encode:")
    print(f"  Encoded:  {encoded}")
    
    # Step 3: Gửi lên server HTTP (POST data)
    print(f"\nStep 3 - HTTP POST gửi lên:")
    print(f"  POST data email={encoded}")
    
    # Step 4: Backend PHP nhận và decode ROT13
    decoded = codecs.encode(encoded, "rot_13")  # ROT13 encode 2 lần = decode
    print(f"\nStep 4 - Backend PHP nhận và str_rot13():")
    print(f"  Decoded:  {decoded}")
    
    # Kiểm tra xem có khớp lại payload gốc không
    print(f"\nVerify - Payload khớp lại không?")
    print(f"  Original == Decoded: {payload == decoded}")
    print(f"  ✓ ĐÚNG!" if payload == decoded else "  ✗ SAI!")

if __name__ == "__main__":
    test_rot13_payload()
