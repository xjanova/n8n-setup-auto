# 🚀 เริ่มต้นใช้งานด่วน N8N

## 📌 ติดตั้งใน 3 ขั้นตอน

### ขั้นตอนที่ 1: ดาวน์โหลด
```bash
wget https://raw.githubusercontent.com/[YOUR-REPO]/sh-install/install-n8n.sh
```

### ขั้นตอนที่ 2: เพิ่มสิทธิ์
```bash
chmod +x install-n8n.sh
```

### ขั้นตอนที่ 3: ติดตั้ง
```bash
sudo ./install-n8n.sh
```

**เท่านี้ก็เสร็จแล้ว!** 🎉

---

## 🌐 เข้าใช้งาน

เปิดเว็บเบราว์เซอร์และไปที่:

```
http://YOUR_SERVER_IP:5678
```

**ตัวอย่าง:**
- หาก IP ของเซิร์ฟเวอร์คุณคือ `192.168.1.100`
- เข้าที่: `http://192.168.1.100:5678`

---

## ⚙️ คำสั่งพื้นฐาน

### เริ่มต้น N8N
```bash
sudo systemctl start n8n
```

### หยุด N8N
```bash
sudo systemctl stop n8n
```

### รีสตาร์ท N8N
```bash
sudo systemctl restart n8n
```

### ตรวจสอบสถานะ
```bash
sudo systemctl status n8n
```

### ดู Logs แบบ Real-time
```bash
sudo journalctl -u n8n -f
```

---

## 🔧 การแก้ไขการตั้งค่า

### เปิดไฟล์การตั้งค่า
```bash
sudo nano /home/n8n/.n8n-env
```

### หลังแก้ไขเสร็จให้รีสตาร์ท
```bash
sudo systemctl restart n8n
```

---

## 🔐 เปิดใช้งาน Authentication (แนะนำ)

### แก้ไขไฟล์
```bash
sudo nano /home/n8n/.n8n-env
```

### เปลี่ยนค่าเหล่านี้
```bash
N8N_BASIC_AUTH_ACTIVE=true
N8N_BASIC_AUTH_USER=admin
N8N_BASIC_AUTH_PASSWORD=รหัสผ่านที่แข็งแรง
```

### รีสตาร์ท
```bash
sudo systemctl restart n8n
```

---

## 💾 สำรองข้อมูล

### สำรองด้วยตนเอง
```bash
sudo tar -czf n8n-backup-$(date +%Y%m%d).tar.gz -C /home/n8n .n8n
```

### ใช้สคริปต์สำรองข้อมูล
```bash
sudo ./backup-n8n.sh
```

---

## 🔄 อัพเดต N8N

```bash
sudo systemctl stop n8n
sudo npm update -g n8n
sudo systemctl start n8n
```

---

## 🐛 แก้ไขปัญหาเบื้องต้น

### ปัญหา: เข้าเว็บไม่ได้

**ตรวจสอบ 1:** N8N ทำงานอยู่หรือไม่?
```bash
sudo systemctl status n8n
```

**ตรวจสอบ 2:** Firewall อนุญาตพอร์ต 5678 หรือไม่?
```bash
sudo ufw allow 5678/tcp
```

**ตรวจสอบ 3:** ดู Logs มีข้อผิดพลาดอะไร
```bash
sudo journalctl -u n8n -n 50
```

### ปัญหา: N8N ไม่เริ่มต้น

**แก้ไข:** ตรวจสอบสิทธิ์ของโฟลเดอร์
```bash
sudo chown -R n8n:n8n /home/n8n/.n8n
sudo systemctl restart n8n
```

---

## 📚 เอกสารเพิ่มเติม

- **คู่มือฉบับเต็ม (ไทย):** [คู่มือติดตั้ง.md](./คู่มือติดตั้ง.md)
- **README (English):** [README.md](./README.md)
- **N8N Official Docs:** https://docs.n8n.io/

---

## 🛠️ เครื่องมือเพิ่มเติม

### สำรองข้อมูล
```bash
sudo ./backup-n8n.sh
```

### ดูรายการไฟล์สำรอง
```bash
sudo ./backup-n8n.sh list
```

### กู้คืนข้อมูล
```bash
sudo ./backup-n8n.sh restore /backup/n8n/n8n-backup-20240101.tar.gz
```

### ถอนการติดตั้ง
```bash
sudo ./uninstall-n8n.sh
```

---

## 📝 เคล็ดลับ

### 1. เปลี่ยนพอร์ต
แก้ไขใน `/home/n8n/.n8n-env`:
```bash
N8N_PORT=8080
```
จากนั้นรีสตาร์ท

### 2. ตั้งค่า Auto Backup ทุกวัน
```bash
sudo crontab -e
```

เพิ่มบรรทัดนี้ (สำรองทุกวัน เวลา 02:00 น.):
```
0 2 * * * /home/user/n8n-setup-auto/sh-install/backup-n8n.sh
```

### 3. ดูขนาดข้อมูล
```bash
du -sh /home/n8n/.n8n
```

### 4. ดูการใช้ทรัพยากร
```bash
top -p $(pgrep -f n8n)
```

---

## ❓ ถามคำถาม

หากมีปัญหา:

1. ✅ อ่าน [คู่มือการแก้ไขปัญหา](./คู่มือติดตั้ง.md#การแก้ไขปัญหา)
2. ✅ ดู logs: `sudo journalctl -u n8n -f`
3. ✅ เข้า N8N Community: https://community.n8n.io/
4. ✅ อ่าน Official Docs: https://docs.n8n.io/

---

## 🎯 สิ่งที่ควรทำหลังติดตั้ง

- [ ] เปิดใช้งาน Basic Authentication
- [ ] ตั้งค่า Auto Backup
- [ ] เปลี่ยน Timezone ให้ถูกต้อง
- [ ] ตั้งค่า Firewall
- [ ] ทดสอบสร้าง Workflow แรก
- [ ] บุ๊กมาร์ก URL ของ N8N
- [ ] อ่านคู่มือฉบับเต็ม

---

**เวอร์ชัน:** 2.0.0
**ภาษา:** ไทย
**สำหรับ:** Ubuntu 20.04, 22.04, 24.04

---

## 🎊 สนุกกับการสร้าง Workflow!

หากคุณต้องการความช่วยเหลือเพิ่มเติม อ่าน[คู่มือฉบับเต็ม](./คู่มือติดตั้ง.md) ซึ่งมีรายละเอียดครบถ้วนทุกเรื่อง

**Happy Automating! 🚀**
