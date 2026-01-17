sudo apt install apache2 mariadb-server php php-mysql -y

sudo raspi-config 

select => interface option


hostname -I (or)
ip addr show (or)
sudo apt install net-tools

# Web server folder copy
sudo cp -r /mnt/hgfs/your_shared_folder_name /var/www/html/attendanceapp

# Apache to known pi
sudo chown -R www-data:www-data /var/www/html/attendanceapp
sudo chmod -R 755 /var/www/html/attendanceapp

# import db
sudo mariadb -u root -e "CREATE DATABASE attendance_db;"

# import sql file
sudo mariadb -u root attendance_db < /mnt/hgfs/your_shared_folder_name/your_sql_file.sql

# if empty folder (/mnt/hgfs/)
sudo apt install open-vm-tools-desktop

# check shared folder
vmware-hgfsclient

sudo apt update
sudo apt install open-vm-tools -y

# folder making
sudo mkdir -p /mnt/hgfs

sudo apt update
sudo apt install fuse3 open-vm-tools-desktop -y

# folder mount
sudo mount -t fuse.vmhgfs-fuse .host:/ /mnt/hgfs -o allow_other


sudo mv ~/Desktop/attendanceapp /var/www/html/
sudo chown -R www-data:www-data /var/www/html/attendanceapp

# go MariaDB 
sudo mariadb -u root

# create Database 
CREATE DATABASE attendance_db;
EXIT;

# import SQL file , check file path
sudo mariadb -u root attendance_db < /var/www/html/attendanceapp/database/your_sql_file.sql



new methods
sudo apt update
sudo apt install open-vm-tools-desktop fuse3 -y

sudo mkdir -p /mnt/hgfs
sudo mount -t fuse.vmhgfs-fuse .host:/ /mnt/hgfs -o allow_other

# Database 
sudo mariadb -u root -e "CREATE DATABASE attendance_db;"

# SQL file , change file name
sudo mariadb -u root attendance_db < /var/www/html/attendanceapp/database/your_db_file.sql



# Downloads ထဲမှာ ရှိနေတယ်ဆိုရင်
sudo cp -r ~/Downloads/attendanceapp /var/www/html/
sudo cp -r ~/Downloads/attendanceapp /var/www/html/

# ပြီးရင် Apache (Web Server) က ဖတ်နိုင်အောင် ပိုင်ရှင်ပြောင်းပေးပါ
sudo chown -R www-data:www-data /var/www/html/attendanceapp
sudo chmod -R 755 /var/www/html/attendanceapp


sudo chown -R www-data:www-data /var/www/html/attendanceapp
sudo chmod -R 755 /var/www/html/attendanceapp

# snapdrop.net

# folder အဆင့်ဆင့်ကို အရင်ဆောက်ပါ
sudo mkdir -p /var/www/html

# ပြီးမှ folder ကို ကူးပါ
sudo cp -r ~/Downloads/attendanceapp /var/www/html/

sudo chown -R www-data:www-data /var/www/html/attendanceapp
sudo chmod -R 755 /var/www/html/attendanceapp

sudo mariadb -u root
CREATE DATABASE attendance_db
EXIT;

sudo mariadb -u root attendance_db < ~/Downloads/attendanceapp/your_file.sql

sudo apt update
sudo apt install mariadb-server -y

sudo systemctl start mariadb
sudo systemctl enable mariadb

sudo mariadb -u root attendance_db < ~/Downloads/attendanceapp/your_sql_file.sql

sudo apt install php php-mysql libapache2-mod-php -y
sudo systemctl restart apache2

sudo rm -rf /var/lib/apt/lists/*
sudo apt update --fix-missing

sudo apt install mariadb-server mariadb-client -y


# IPv4 
sudo apt-get update -o Acquire::ForceIPv4=true
(or)
sudo apt-get install mariadb-server php-mysql -y -o Acquire::ForceIPv4=true

sudo mariadb -u root

# copy folder to Web Server 
sudo cp -r ~/Downloads/attendanceapp /var/www/html/

# give command to apache
sudo chown -R www-data:www-data /var/www/html/attendanceapp
sudo chmod -R 755 /var/www/html/attendanceapp

sudo apt update
sudo apt install mariadb-server php php-mysql libapache2-mod-php apache2 -y

sudo mariadb -u root -e "CREATE DATABASE attendance_db;"

sudo mariadb -u root attendance_db < /var/www/html/attendanceapp/your_sql_file.sql

sudo ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin
sudo systemctl restart apache2

# PHP module ကို သွင်းပြီး အလုပ်ပေးရန်
sudo apt install libapache2-mod-php -y

# Apache မှာ PHP ကို ပွင့်သွားအောင် လုပ်ရန်
sudo a2enmod php7.4

sudo systemctl restart apache2

sudo nano /etc/phpmyadmin/config.inc.php

sudo mariadb -u root

ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('123456');
FLUSH PRIVILEGES;
EXIT;