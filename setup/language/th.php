<?php
/**
 * Thai Language File
 */

return [
    // General
    'app_name' => 'ตัวติดตั้ง N8N',
    'welcome' => 'ยินดีต้อนรับ',
    'next' => 'ถัดไป',
    'back' => 'ย้อนกลับ',
    'install' => 'ติดตั้ง',
    'finish' => 'เสร็จสิ้น',
    'cancel' => 'ยกเลิก',
    'yes' => 'ใช่',
    'no' => 'ไม่',
    'required' => 'จำเป็น',
    'optional' => 'ไม่บังคับ',
    'recommended' => 'แนะนำ',

    // Company Info
    'company_name' => 'Xman Enterprise co.,ltd.',
    'company_website' => 'https://xman4289.com',
    'company_phone' => '(066) 080-6038278',
    'powered_by' => 'พัฒนาโดย',
    'version' => 'เวอร์ชั่น',

    // Steps
    'step_welcome' => 'ยินดีต้อนรับ',
    'step_requirements' => 'ตรวจสอบความพร้อม',
    'step_database' => 'ฐานข้อมูล',
    'step_configuration' => 'ตั้งค่าระบบ',
    'step_installation' => 'ติดตั้ง',
    'step_complete' => 'เสร็จสิ้น',

    // Welcome Page
    'welcome_title' => 'ยินดีต้อนรับสู่ตัวติดตั้ง N8N',
    'welcome_subtitle' => 'ระบบอัตโนมัติเวิร์กโฟลว์สำหรับมืออาชีพ',
    'welcome_description' => 'ตัวติดตั้งนี้จะช่วยคุณติดตั้งและกำหนดค่า N8N บนเซิร์ฟเวอร์ของคุณอย่างง่ายดาย กรุณาตรวจสอบให้แน่ใจว่าคุณได้เตรียมข้อมูลต่อไปนี้:',
    'welcome_requirements' => [
        'เซิร์ฟเวอร์ PHP เวอร์ชั่น 7.4 หรือสูงกว่า',
        'Node.js เวอร์ชั่น 18.0 หรือสูงกว่า',
        'ฐานข้อมูล MySQL, PostgreSQL หรือ SQLite',
        'สิทธิ์ในการเขียนไฟล์บนเซิร์ฟเวอร์',
        'การเชื่อมต่ออินเทอร์เน็ตสำหรับดาวน์โหลด N8N'
    ],
    'welcome_start' => 'เริ่มการติดตั้ง',
    'select_language' => 'เลือกภาษา',

    // Requirements Check
    'requirements_title' => 'ตรวจสอบความพร้อมของระบบ',
    'requirements_description' => 'กรุณาตรวจสอบให้แน่ใจว่าระบบของคุณตรงตามข้อกำหนดทั้งหมด',
    'requirements_checking' => 'กำลังตรวจสอบความพร้อม...',
    'requirements_passed' => 'ผ่านการตรวจสอบ',
    'requirements_failed' => 'ไม่ผ่านการตรวจสอบ',
    'requirements_warning' => 'คำเตือน',
    'php_version' => 'เวอร์ชั่น PHP',
    'php_extensions' => 'PHP Extensions',
    'file_permissions' => 'สิทธิ์ในการเขียนไฟล์',
    'node_version' => 'เวอร์ชั่น Node.js',
    'npm_version' => 'เวอร์ชั่น NPM',
    'disk_space' => 'พื้นที่ว่างในดิสก์',
    'memory_limit' => 'ขีดจำกัดหน่วยความจำ',
    'max_execution_time' => 'เวลาประมวลผลสูงสุด',

    // Database Configuration
    'database_title' => 'การตั้งค่าฐานข้อมูล',
    'database_description' => 'กรุณากรอกข้อมูลสำหรับเชื่อมต่อฐานข้อมูล',
    'database_type' => 'ประเภทฐานข้อมูล',
    'database_host' => 'โฮสต์',
    'database_port' => 'พอร์ต',
    'database_name' => 'ชื่อฐานข้อมูล',
    'database_username' => 'ชื่อผู้ใช้',
    'database_password' => 'รหัสผ่าน',
    'database_prefix' => 'คำนำหน้าตาราง',
    'database_test' => 'ทดสอบการเชื่อมต่อ',
    'database_testing' => 'กำลังทดสอบการเชื่อมต่อ...',
    'database_success' => 'เชื่อมต่อสำเร็จ!',
    'database_error' => 'ไม่สามารถเชื่อมต่อได้',

    // N8N Configuration
    'n8n_title' => 'การตั้งค่า N8N',
    'n8n_description' => 'กำหนดค่าพื้นฐานสำหรับ N8N ของคุณ',
    'n8n_url' => 'URL ของ N8N',
    'n8n_port' => 'พอร์ต',
    'n8n_admin_email' => 'อีเมลผู้ดูแลระบบ',
    'n8n_admin_password' => 'รหัสผ่านผู้ดูแลระบบ',
    'n8n_admin_password_confirm' => 'ยืนยันรหัสผ่าน',
    'n8n_timezone' => 'เขตเวลา',
    'n8n_encryption_key' => 'กุญแจเข้ารหัส',
    'n8n_generate_key' => 'สร้างกุญแจอัตโนมัติ',
    'install_location' => 'ตำแหน่งการติดตั้ง',
    'install_location_hint' => 'ระบุพาธที่ต้องการติดตั้ง (เว้นว่างเพื่อใช้ค่าเริ่มต้น)',

    // Installation Process
    'installation_title' => 'กำลังติดตั้ง N8N',
    'installation_description' => 'กรุณารอสักครู่ กระบวนการนี้อาจใช้เวลาสักครู่...',
    'installation_step_download' => 'ดาวน์โหลด N8N',
    'installation_step_extract' => 'แตกไฟล์',
    'installation_step_database' => 'สร้างฐานข้อมูล',
    'installation_step_config' => 'สร้างไฟล์กำหนดค่า',
    'installation_step_dependencies' => 'ติดตั้ง Dependencies',
    'installation_step_finalize' => 'ตั้งค่าขั้นสุดท้าย',
    'installation_progress' => 'ความคืบหน้า',

    // Installation Complete
    'complete_title' => 'การติดตั้งเสร็จสมบูรณ์!',
    'complete_subtitle' => 'ยินดีด้วย! N8N ของคุณพร้อมใช้งานแล้ว',
    'complete_description' => 'การติดตั้งเสร็จสมบูรณ์ คุณสามารถเข้าใช้งาน N8N ได้แล้ว',
    'complete_info' => 'ข้อมูลการติดตั้ง',
    'complete_url' => 'URL เข้าสู่ระบบ',
    'complete_admin_email' => 'อีเมลผู้ดูแลระบบ',
    'complete_next_steps' => 'ขั้นตอนถัดไป',
    'complete_login' => 'เข้าสู่ระบบ N8N',
    'complete_documentation' => 'อ่านคู่มือการใช้งาน',
    'complete_cleanup' => 'ลบไฟล์ติดตั้งทันที',
    'complete_cleanup_warning' => 'เพื่อความปลอดภัย โฟลเดอร์ setup จะถูกลบหลังจากที่คุณคลิก "เสร็จสิ้น"',

    // Tips
    'tip_prefix' => 'เคล็ดลับ',
    'tips' => [
        'ตรวจสอบให้แน่ใจว่าฐานข้อมูลของคุณได้รับการสำรองข้อมูลก่อนการติดตั้ง',
        'ใช้รหัสผ่านที่รัดกุมสำหรับบัญชีผู้ดูแลระบบ',
        'เก็บกุญแจเข้ารหัสไว้ในที่ปลอดภัย คุณจะต้องใช้เพื่อกู้คืนข้อมูล',
        'Node.js เวอร์ชั่น LTS แนะนำเพื่อความเสถียรสูงสุด',
        'อัปเดต N8N เป็นประจำเพื่อรับฟีเจอร์และแพตช์ความปลอดภัยใหม่ล่าสุด',
        'กำหนดค่า Firewall เพื่ออนุญาตพอร์ตที่ N8N ใช้งาน',
        'ใช้ HTTPS สำหรับการเข้าถึง N8N ในสภาพแวดล้อมการผลิต',
        'ตั้งค่า Webhook URL ที่ถูกต้องสำหรับการทำงานอัตโนมัติ'
    ],

    // Errors
    'error_title' => 'เกิดข้อผิดพลาด',
    'error_general' => 'เกิดข้อผิดพลาด กรุณาลองอีกครั้ง',
    'error_permission' => 'ไม่มีสิทธิ์ในการเขียนไฟล์',
    'error_database' => 'ไม่สามารถเชื่อมต่อฐานข้อมูล',
    'error_download' => 'ไม่สามารถดาวน์โหลด N8N',
    'error_extraction' => 'ไม่สามารถแตกไฟล์',
    'error_configuration' => 'ไม่สามารถสร้างไฟล์กำหนดค่า',
    'error_node_not_found' => 'ไม่พบ Node.js กรุณาติดตั้ง Node.js ก่อน',
    'error_npm_not_found' => 'ไม่พบ NPM กรุณาติดตั้ง NPM ก่อน',
    'error_csrf' => 'CSRF token ไม่ถูกต้อง',

    // Success Messages
    'success_general' => 'สำเร็จ!',
    'success_database' => 'ฐานข้อมูลถูกสร้างเรียบร้อยแล้ว',
    'success_configuration' => 'การกำหนดค่าสำเร็จ',
    'success_installation' => 'การติดตั้งสำเร็จ',
    'success_cleanup' => 'โฟลเดอร์ติดตั้งถูกลบเรียบร้อยแล้ว',
];
