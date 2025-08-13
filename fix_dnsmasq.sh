#!/bin/bash

echo "🛑 หยุด dnsmasq และลบไฟล์เก่า..."
sudo pkill dnsmasq
brew services stop dnsmasq
sudo rm -rf /opt/homebrew/Cellar/dnsmasq
sudo rm -rf /opt/homebrew/etc/dnsmasq.conf
sudo rm -rf /opt/homebrew/var/run/dnsmasq.pid
sudo rm -rf /etc/resolver/test

echo "✅ ติดตั้ง dnsmasq ใหม่..."
brew install dnsmasq

echo "⚙️ ตั้งค่า dnsmasq ให้รองรับ .test..."
sudo mkdir -p /etc/resolver
echo "nameserver 127.0.0.1" | sudo tee /etc/resolver/test >/dev/null
echo 'address=/.test/127.0.0.1' > $(brew --prefix)/etc/dnsmasq.conf

echo "🔄 Start dnsmasq..."
sudo brew services start dnsmasq

echo "🔄 Restart Valet..."
valet restart

echo "🔒 Secure domain ตัวอย่าง..."
valet secure api-ibs

echo "🧹 ล้ง  DNS cache..."
sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder

echo "🎉 เสร็จสิ้น! ลองเปิด https://api-ibs.test"
