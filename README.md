# Complete Customer & Crypto Management System

---

## Project Description

A comprehensive web application built with PHP and MySQL for managing customer data. This application features a complete, secure, dual-login system for both administrators and customers. Key features include a professional, responsive user interface styled with Bootstrap, a full suite of CRUD operations, advanced admin panels, and a dynamic customer dashboard that fetches live data from an external cryptocurrency API. The entire application supports both English and Thai languages.

### คำอธิบายโปรเจกต์
เว็บแอปพลิเคชันที่สมบูรณ์แบบสำหรับจัดการข้อมูลลูกค้า สร้างขึ้นด้วย PHP และ MySQL ประกอบด้วยระบบล็อกอิน 2 ระบบที่แยกจากกันและปลอดภัยสำหรับผู้ดูแลระบบและลูกค้า โดดเด่นด้วยหน้าตาเว็บที่ดูเป็นมืออาชีพและรองรับทุกขนาดหน้าจอ (Responsive) ด้วย Bootstrap, มีฟังก์ชันการจัดการข้อมูลครบถ้วน (CRUD), แผงควบคุมสำหรับแอดมินขั้นสูง, และแดชบอร์ดสำหรับลูกค้าที่ดึงข้อมูลสดจาก API ภายนอก แอปพลิเคชันทั้งหมดรองรับการใช้งานสองภาษา (อังกฤษและไทย)

---

## Core Features

### 1. Customer Management System
-   **Public Registration:** A public-facing registration form protected by Google reCAPTCHA.
-   **Admin CRUD Operations:** A private admin panel to View (with search), Edit, and Delete customer records.
-   **Self-Service Profile Editing:** Logged-in customers can edit their own profile information (name, email, phone).

#### **ระบบจัดการลูกค้า**
-   **การลงทะเบียนสาธารณะ:** ฟอร์มลงทะเบียนสำหรับลูกค้าใหม่ที่เป็นสาธารณะ พร้อมระบบป้องกันบอท Google reCAPTCHA
-   **การจัดการโดยแอดมิน (CRUD):** แผงควบคุมส่วนตัวสำหรับแอดมินเพื่อ ดู (พร้อมระบบค้นหา), แก้ไข, และลบ ข้อมูลลูกค้า
-   **การแก้ไขโปรไฟล์ด้วยตนเอง:** ลูกค้าที่ล็อกอินแล้วสามารถแก้ไขข้อมูลส่วนตัวของตนเองได้ (ชื่อ, อีเมล, เบอร์โทร)

### 2. Dual Authentication System
-   Separate, secure login/logout systems for Admins (`users` table) and Customers (`customers` table).
-   All passwords are securely stored using `password_hash()` and verified with `password_verify()`.
-   PHP Sessions manage the login state for both user types independently.
-   All sensitive pages are protected and require the correct login type.

#### **ระบบยืนยันตัวตน 2 ระบบ**
-   ระบบล็อกอิน/ล็อกเอาต์ที่แยกจากกันและปลอดภัย สำหรับ 'ผู้ดูแลระบบ' (จากตาราง `users`) และ 'ลูกค้า' (จากตาราง `customers`)
-   รหัสผ่านทั้งหมดถูกเก็บอย่างปลอดภัยด้วยการเข้ารหัส (Hashing) และตรวจสอบด้วย `password_verify()`
-   ใช้ PHP Session ในการจดจำสถานะการล็อกอินของ User ทั้งสองประเภทแยกจากกัน
-   ทุกหน้าที่เป็นข้อมูลส่วนตัวจะถูกป้องกันและต้องการการล็อกอินที่ถูกประเภทก่อนเข้าใช้งาน

### 3. Administrator Panel
-   **User (Admin) Management:** Admins can View, Add, and Delete other administrator accounts.
-   **Admin Password Reset:** Admins can securely reset the password for other admin accounts.
-   **Activity Logging:** The system automatically records the timestamp and IP address of every successful admin and customer login for auditing.

#### **แผงควบคุมสำหรับผู้ดูแลระบบ**
-   **การจัดการผู้ใช้ (แอดมิน):** แอดมินสามารถ ดู, เพิ่ม, และลบ บัญชีของแอดมินคนอื่นได้
-   **การรีเซ็ตรหัสผ่านแอดมิน:** แอดมินสามารถตั้งรหัสผ่านใหม่ให้แอดมินคนอื่นได้อย่างปลอดภัย
-   **การบันทึกกิจกรรม:** ระบบจะบันทึกเวลาและ IP Address ของการล็อกอินสำเร็จของทั้งแอดมินและลูกค้าโดยอัตโนมัติเพื่อการตรวจสอบ

### 4. Customer Dashboard & Portfolio System
-   A private, customer-only "Ultimate Dashboard" accessible after login.
-   **Live Crypto Data:** Features a sidebar with live cryptocurrency prices fetched from the CoinGecko API.
-   **Interactive Chart:** A large, central chart using Chart.js that dynamically updates to show the 30-day price history when a user hovers over a coin in the sidebar (AJAX).
-   **Currency Converter:** A functional, real-time currency converter built with JavaScript.
-   **Portfolio Management:** Customers can add their 'buy'/'sell' transactions.
-   **Portfolio Calculation Engine:** The system automatically calculates total holdings, current market value, and profit/loss for the customer's portfolio based on live prices.
-   **Profile Page:** A dedicated page for customers to view their account details and navigate to the edit page.

#### **แดชบอร์ดและระบบพอร์ตฟอลิโอสำหรับลูกค้า**
-   "สุดยอดแดชบอร์ด" ส่วนตัวสำหรับลูกค้าที่เข้าได้หลังล็อกอินเท่านั้น
-   **ข้อมูลคริปโตล่าสุด:** ประกอบด้วยแถบด้านข้างแสดงราคาเหรียญล่าสุดจาก CoinGecko API
-   **กราฟแบบโต้ตอบ:** กราฟขนาดใหญ่ตรงกลางที่ใช้ Chart.js ซึ่งจะอัปเดตเพื่อแสดงประวัติราคา 30 วันโดยอัตโนมัติเมื่อผู้ใช้เลื่อนเมาส์ไปเหนือเหรียญในแถบด้านข้าง (AJAX)
-   **เครื่องมือแปลงสกุลเงิน:** เครื่องมือแปลงสกุลเงินที่ใช้งานได้จริงแบบเรียลไทม์ สร้างด้วย JavaScript
-   **การจัดการพอร์ต:** ลูกค้าสามารถเพิ่มประวัติการ 'ซื้อ'/'ขาย' ของตนเองได้
-   **กลไกคำนวณพอร์ต:** ระบบจะคำนวณจำนวนเหรียญที่ถือครอง, มูลค่าตลาดปัจจุบัน, และกำไร/ขาดทุน ของพอร์ตลูกค้าโดยอัตโนมัติ
-   **หน้าโปรไฟล์:** หน้าเฉพาะสำหรับลูกค้าในการดูรายละเอียดบัญชีและนำทางไปยังหน้าแก้ไข 