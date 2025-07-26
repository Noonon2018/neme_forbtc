# NEME_FORBTC: Complete Customer & Crypto Management System

---

## Overview

A robust web application built with **PHP** and **MySQL** for comprehensive customer and cryptocurrency portfolio management. Featuring a secure dual-login system, responsive UI with **Bootstrap**, advanced admin controls, and a dynamic customer dashboard with live crypto data. Supports both **English** and **Thai**.

---

## Project Highlights

- **Dual Authentication:** Separate, secure login systems for admins and customers.
- **Admin Panel:** Full admin management, password reset, and activity logging.
- **Customer Dashboard:** Live crypto prices, interactive charts, real-time currency converter, and portfolio management.
- **Modern UI:** Responsive, professional design with Bootstrap.
- **Bilingual Support:** English & Thai interfaces.

---

## Core Features

### 1. Customer Management System

- **Public Registration:** Secure registration form with Google reCAPTCHA.
- **Admin CRUD:** Admins can view (with search), edit, and delete customer records.
- **Self-Service Profile:** Customers can update their own profile (name, email, phone).

#### ระบบจัดการลูกค้า

- **ลงทะเบียนลูกค้าใหม่:** ฟอร์มลงทะเบียนพร้อม Google reCAPTCHA
- **แอดมินจัดการข้อมูล:** ดู (ค้นหา), แก้ไข, ลบข้อมูลลูกค้า
- **ลูกค้าแก้ไขโปรไฟล์:** แก้ไขชื่อ, อีเมล, เบอร์โทรได้เอง

---

### 2. Dual Authentication System

- **Separate Logins:** Distinct login/logout for admins (`users` table) and customers (`customers` table).
- **Secure Passwords:** All passwords hashed with `password_hash()` and verified with `password_verify()`.
- **Session Management:** PHP Sessions keep admin and customer logins separate.
- **Protected Pages:** Sensitive pages require correct login type.

#### ระบบยืนยันตัวตน 2 ระบบ

- **ล็อกอินแยก:** สำหรับแอดมินและลูกค้า
- **รหัสผ่านปลอดภัย:** เข้ารหัสและตรวจสอบด้วย `password_hash()`/`password_verify()`
- **จัดการ Session:** แยกสถานะล็อกอินแต่ละประเภท
- **ป้องกันหน้าสำคัญ:** ต้องล็อกอินถูกประเภทก่อนเข้าใช้งาน

---

### 3. Administrator Panel

- **Admin Management:** View, add, and delete other admin accounts.
- **Password Reset:** Securely reset admin passwords.
- **Activity Logging:** Automatically records login time and IP for admins and customers.

#### แผงควบคุมแอดมิน

- **จัดการแอดมิน:** ดู, เพิ่ม, ลบบัญชีแอดมิน
- **รีเซ็ตรหัสผ่าน:** ตั้งรหัสใหม่ให้แอดมินคนอื่น
- **บันทึกกิจกรรม:** บันทึกเวลาและ IP การล็อกอินของแอดมินและลูกค้า

---

### 4. Customer Dashboard & Portfolio

- **Ultimate Dashboard:** Private dashboard for logged-in customers.
- **Live Crypto Data:** Sidebar with real-time prices from CoinGecko API.
- **Interactive Chart:** 30-day price history with Chart.js (AJAX updates).
- **Currency Converter:** Real-time conversion tool (JavaScript).
- **Portfolio Management:** Customers add buy/sell transactions.
- **Auto Portfolio Calculation:** Calculates holdings, market value, and profit/loss.
- **Profile Page:** View and edit account details.

#### แดชบอร์ดและระบบพอร์ตลูกค้า

- **แดชบอร์ดส่วนตัว:** สำหรับลูกค้าที่ล็อกอิน
- **ราคาคริปโตสด:** ดึงข้อมูลจาก CoinGecko API
- **กราฟโต้ตอบ:** ประวัติราคา 30 วัน (Chart.js + AJAX)
- **แปลงสกุลเงิน:** เครื่องมือแปลงแบบเรียลไทม์
- **จัดการพอร์ต:** เพิ่มประวัติซื้อ/ขาย
- **คำนวณพอร์ตอัตโนมัติ:** ถือครอง, มูลค่าตลาด, กำไร/ขาดทุน
- **หน้าโปรไฟล์:** ดูและแก้ไขข้อมูลบัญชี

---

## Tech Stack

- **Backend:** PHP 7+, MySQL
- **Frontend:** Bootstrap 4+, Chart.js, AJAX, JavaScript
- **APIs:** CoinGecko (crypto prices)
- **Security:** Google reCAPTCHA, password hashing, session management

---

## Getting Started

1. Clone this repository.
2. Import the SQL schema into your MySQL database.
3. Configure database credentials in `/config/db.php`.
4. Set up Google reCAPTCHA keys.
5. Deploy on your PHP server.

---

## License

MIT License

---

> For more details, see the source code and comments in each file.