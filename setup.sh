#!/bin/bash

# HiveNova System Setup Script
echo -e "\033[34m"
echo "   __   __  _______  __   __  ___   _______  ___      ___ "
echo "  |  |_|  ||       ||  | |  ||   | |       ||   |    |   |"
echo "  |       ||    ___||  |_|  ||   | |_     _||   |    |   |"
echo "  |       ||   |___ |       ||   |   |   |  |   |    |   |"
echo "  |       ||    ___||_     _||   |   |   |  |   |___ |   |"
echo "  | ||_|| ||   |___   |   |  |   |   |   |  |       ||   |"
echo "  |_|   |_||_______|  |___|  |___|   |___|  |_______||___|"
echo -e "\033[0m"

# Check for root privileges
if [ "$(id -u)" -ne 0 ]; then
  echo -e "\033[31m✘ Root privileges are required to run this setup script.\033[0m"
  exit 1
fi

# Update package lists
echo -e "\033[33mUpdating package lists...\033[0m"
apt-get update

# Install required packages: mysql-server, php, php-mysql
echo -e "\033[33mInstalling MySQL server, PHP, and PHP MySQL extension...\033[0m"
if apt-cache show mysql-server &> /dev/null; then
    apt-get install -y mysql-server php php-mysql
else
    echo -e "\033[33mMySQL server package not found, installing default-mysql-server...\033[0m"
    apt-get install -y default-mysql-server php php-mysql
fi

# Ensure MySQL service is running
echo -e "\033[33mStarting MySQL service if not running...\033[0m"
if ! systemctl is-active --quiet mysql; then
    systemctl start mysql || {
        echo -e "\033[33mFailed to start MySQL service normally, trying with sudo...\033[0m"
        sudo systemctl start mysql
    }
fi

# Create database and user with privileges
echo -e "\033[33mCreating database and user...\033[0m"
DB_NAME="hivenova"
DB_USER="vulnuser"
DB_PASS="vulnpassword"

# Try to create database and user without password first
mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" 2>/dev/null
if [ $? -ne 0 ]; then
    echo -e "\033[33mMySQL root access required. Please enter MySQL root password:\033[0m"
    read -s -p "MySQL root password: " MYSQL_ROOT_PASS
    echo ""
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" || {
        echo -e "\033[31mFailed to create database with provided password. Trying with sudo...\033[0m"
        sudo mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"
    }
fi

# Create user and grant privileges
if [ -z "$MYSQL_ROOT_PASS" ]; then
    mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" 2>/dev/null
    if [ $? -ne 0 ]; then
        echo -e "\033[33mMySQL root access required to create user. Please enter MySQL root password:\033[0m"
        read -s -p "MySQL root password: " MYSQL_ROOT_PASS
        echo ""
    fi
fi

if [ -n "$MYSQL_ROOT_PASS" ]; then
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" || {
        echo -e "\033[31mFailed to create user with provided password. Trying with sudo...\033[0m"
        sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
    }
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || {
        echo -e "\033[31mFailed to grant privileges with provided password. Trying with sudo...\033[0m"
        sudo mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    }
    mysql -u root -p"$MYSQL_ROOT_PASS" -e "FLUSH PRIVILEGES;" || {
        echo -e "\033[31mFailed to flush privileges with provided password. Trying with sudo...\033[0m"
        sudo mysql -e "FLUSH PRIVILEGES;"
    }
else
    mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" && \
    mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" && \
    mysql -e "FLUSH PRIVILEGES;" || {
        echo -e "\033[31mDatabase operations failed! Please run with sudo or provide MySQL root password.\033[0m"
        exit 1
    }
fi

# Import SQL schema files
echo -e "\033[33mImporting database schema...\033[0m"
SQL_FILES=(
    "create_db_user.sql"
    "create_messages_table.sql"
    "create_is_read_column.sql"
    "create_lab_tables.sql"
    "create_appointment_schedules_table.sql"
    "hospital_staff.sql"
)

for sql_file in "${SQL_FILES[@]}"; do
    if [ -f "$sql_file" ]; then
        echo -e "\033[33mImporting $sql_file...\033[0m"
        if [ -n "$MYSQL_ROOT_PASS" ]; then
            mysql -u root -p"$MYSQL_ROOT_PASS" $DB_NAME < "$sql_file" || {
                echo -e "\033[33mWarning: Error importing $sql_file, continuing...\033[0m"
            }
        else
            mysql $DB_NAME < "$sql_file" || {
                echo -e "\033[33mWarning: Error importing $sql_file, continuing...\033[0m"
            }
        fi
    else
        echo -e "\033[31mSQL file $sql_file not found!\033[0m"
    fi
done

# Set file permissions if config file exists
if [ -f "includes/config.php" ]; then
    echo -e "\033[33mSetting permissions on includes/config.php...\033[0m"
    chmod 600 includes/config.php
fi

echo -e "\033[32m"
echo "╔══════════════════════════════════════════╗"
echo "║  HiveNova System successfully deployed!  ║"
echo "╚══════════════════════════════════════════╝"
echo -e "\033[0m"

echo -e "\033[36mStarting PHP built-in server at http://127.0.0.1:9000\033[0m"
php -S 127.0.0.1:9000
