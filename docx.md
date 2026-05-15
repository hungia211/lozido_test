# Hướng Dẫn Cài Đặt Docker Lozido Trên Windows

Tài liệu này hướng dẫn cài project Docker Lozido từ đầu đến cuối trên Windows bằng Docker Desktop. Bộ Docker gồm:

- `nginx`: web server.
- `php-fpm`: PHP 7.4 FPM, Composer, Node.js 14, PM2, các extension PHP.
- `mysql`: MySQL 8.0.19.
- `certbot`: tạo SSL Let's Encrypt, chỉ dùng khi chạy domain thật/public server.

> Repo này là hạ tầng Docker, không phải source app. Source Laravel/PHP phải đặt cùng cấp với thư mục Docker vì `docker-compose.yml` mount thư mục cha `./..` vào `/var/www`.

## 1. Yêu Cầu Trên Windows

Cần cài trước:

- Windows 10/11 64-bit.
- Docker Desktop for Windows.
- WSL2 enabled.
- Git for Windows.
- PowerShell hoặc Windows Terminal.

Khi cài Docker Desktop:

- Chọn backend `WSL 2`.
- Bật chế độ `Linux containers`, không dùng Windows containers.
- Nếu project nằm ở ổ đĩa khác ổ hệ điều hành, kiểm tra Docker Desktop có quyền truy cập ổ đó.

Kiểm tra sau khi cài:

```powershell
docker --version
docker compose version
docker ps
```

Nếu `docker ps` chạy được là Docker Desktop đã sẵn sàng.

## 2. Cấu Trúc Thư Mục Khuyến Nghị

Trong tài liệu này dùng quy ước:

- `<WORKSPACE>`: thư mục cha chứa repo Docker và source app, ví dụ `D:\project`, `E:\project`, `C:\Users\YourName\project`.
- `docker-lozido`: tên thư mục repo Docker.
- `<APP_DIR>`: tên thư mục source app Laravel/PHP, ví dụ `lozido_renter_backend`.

Khi chạy lệnh, hãy thay `<WORKSPACE>` và `<APP_DIR>` bằng đường dẫn/tên thư mục thật trên máy của bạn.

Ví dụ cấu trúc:

```text
<WORKSPACE>\
|-- docker-lozido\              # repo Docker này
`-- <APP_DIR>\                  # source Laravel/PHP app
    `-- public\
```

Khi vào container, thư mục `<WORKSPACE>` sẽ được mount thành `/var/www`, nên các đường dẫn trong container sẽ là:

```text
/var/www/docker-lozido
/var/www/<APP_DIR>/public
```

Nếu source app của bạn tên `lozido_renter_backend`, thì `<APP_DIR>` là `lozido_renter_backend`. Nếu tên khác, sửa lại `root` trong file Nginx ở bước bên dưới.

## 3. Lấy Source Project

Mở PowerShell:

```powershell
cd <WORKSPACE>
```

Clone hoặc copy repo Docker:

```powershell
git clone <URL_REPO_DOCKER> docker-lozido
```

Clone hoặc copy source app Laravel/PHP cùng cấp:

```powershell
git clone <URL_REPO_APP> <APP_DIR>
```

Ví dụ:

```powershell
cd D:\project
git clone <URL_REPO_DOCKER> docker-lozido
git clone <URL_REPO_APP> lozido_renter_backend
```

Nếu bạn đã có repo Docker, chỉ cần đổi tên thư mục thành `docker-lozido` hoặc đảm bảo các đường dẫn trong Nginx/Certbot khớp với tên thư mục thực tế.

## 4. Tạo File Cấu Hình Từ File Mẫu

Vào thư mục Docker:

```powershell
cd <WORKSPACE>\docker-lozido
```

Copy file môi trường:

```powershell
Copy-Item .env.example .env
```

Copy file compose:

```powershell
Copy-Item docker-compose.yml.dev.example docker-compose.yml
```

Copy cấu hình MySQL:

```powershell
Copy-Item mysql\conf.d\my.cnf.example mysql\conf.d\my.cnf
Copy-Item mysql\init\initialization.sql.example mysql\init\initialization.sql
```

Copy cấu hình Nginx chính:

```powershell
Copy-Item nginx\nginx.conf.example nginx\nginx.conf
```

Tạo thư mục Nginx site local:

```powershell
New-Item -ItemType Directory -Force nginx\sites
```

Copy cấu hình PHP-FPM:

```powershell
Copy-Item php\fpm\www.conf.example php\fpm\www.conf
```

Tạo các thư mục runtime:

```powershell
New-Item -ItemType Directory -Force mysql\data
New-Item -ItemType Directory -Force nginx\letsencrypt
New-Item -ItemType Directory -Force nginx\letsencrypt_log
New-Item -ItemType Directory -Force webroot
New-Item -ItemType Directory -Force ssh
```

## 5. Chỉnh File `.env`

Mở file `.env` bằng VS Code hoặc Notepad:

```powershell
notepad .env
```

Nội dung khuyến nghị cho Windows local:

```dotenv
DB_HOST=mysql
DB_PORT=3306

DB_ROOT_USER=lozido_dev
DB_ROOT_PASSWORD='lozido_dev_password'

IP_EXTERNAL=127.0.0.1
NGINX_HOST='localhost'

NGINX_PORT='80'
NGINX_PORT_SSL='443'

NGINX_SITES_PATH='./nginx/sites/'
NGINX_LETSENCRYPT_PATH='./nginx/letsencrypt/'
NGINX_LETSENCRYPT_LOG_PATH='./nginx/letsencrypt_log/'

PHP_PATH_CONFIG='./php/conf.d/local.ini'
FPM_PATH_CONFIG='./php/fpm/www.conf'
SSH_CONFIG='./ssh'
```

Ghi chú:

- App Laravel kết nối MySQL bằng host `mysql`, không dùng `localhost`.
- MySQL được map ra Windows port `33066`, nên từ máy Windows có thể kết nối bằng `127.0.0.1:33066`.
- `DB_ROOT_USER` trong compose hiện tại không tạo root user mới; root user của MySQL vẫn là `root`.

## 6. Chỉnh PHP-FPM Bắt Buộc

Nginx trong project gọi PHP qua `php-fpm:9000`, nên PHP-FPM phải listen port `9000`.

Mở file:

```powershell
notepad php\fpm\www.conf
```

Tìm dòng:

```ini
listen = /var/run/php5-fpm.sock
```

Đổi thành:

```ini
listen = 9000
```

Nếu không đổi dòng này, Nginx sẽ lỗi khi xử lý file PHP.

## 7. Tạo Nginx Site Local HTTP

Các file `nginx\sites.example` và `nginx\sites.dev.example` đang cấu hình HTTPS với certificate thật. Trên Windows local, nên dùng HTTP trước để chạy được ngay.

Tạo file:

```powershell
notepad nginx\sites\lozido_local.conf
```

Dán nội dung sau:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name localhost lozido.local;

    charset utf-8;
    root /var/www/<APP_DIR>/public;
    index index.php index.html index.htm;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }

    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2|webp|svg)$ {
        expires 1M;
        add_header Cache-Control public;
        add_header Pragma public;
        add_header Vary Accept-Encoding;
    }

    location ~ \.php$ {
        fastcgi_pass php-fpm:9000;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
    }

    error_log /var/log/nginx/lozido_local_error.log;
    access_log /var/log/nginx/lozido_local_access.log;
}
```

Trước khi lưu file, thay `<APP_DIR>` bằng tên thư mục source app thật.

Sửa dòng này theo đúng tên thư mục source app trong `<WORKSPACE>`:

```nginx
root /var/www/<APP_DIR>/public;
```

Ví dụ source app là `<WORKSPACE>\lozido_renter_backend`, đổi thành:

```nginx
root /var/www/lozido_renter_backend/public;
```

## 8. Cấu Hình MySQL Init

File `mysql\init\initialization.sql` chỉ chạy lần đầu khi `mysql\data` chưa có dữ liệu.

Mở file:

```powershell
notepad mysql\init\initialization.sql
```

Sửa database, user, password theo app:

```sql
CREATE DATABASE lozido_renter_db_name CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'lozido_renter_user_name'@'%' IDENTIFIED BY 'strong_password';

GRANT ALL PRIVILEGES ON lozido_renter_db_name.* TO 'lozido_renter_user_name'@'%';

FLUSH PRIVILEGES;
```

Thông tin này phải khớp với `.env` của app Laravel ở bước 12.

## 9. Tạo Docker Network

Compose đang dùng external network `app_subnet`, nên phải tạo trước:

```powershell
docker network create --gateway 172.16.1.1 --subnet 172.16.1.0/24 app_subnet
```

Nếu network đã tồn tại, Docker sẽ báo lỗi. Có thể kiểm tra:

```powershell
docker network ls
```

## 10. Build Và Chạy Container

Chạy các service chính:

```powershell
docker compose up -d --build mysql php-fpm nginx
```

Không chạy `certbot` khi cài local Windows, vì Certbot cần domain public trỏ về máy.

Kiểm tra trạng thái:

```powershell
docker compose ps
```

Xem log nếu có lỗi:

```powershell
docker compose logs -f nginx
docker compose logs -f php-fpm
docker compose logs -f mysql
```

Kiểm tra Nginx config:

```powershell
docker compose exec nginx nginx -t
```

## 11. Cấu Hình Domain Local Tùy Chọn

Nếu muốn truy cập bằng `http://lozido.local` thay vì `http://localhost`, mở Notepad bằng quyền Administrator:

```powershell
Start-Process notepad C:\Windows\System32\drivers\etc\hosts -Verb RunAs
```

Thêm dòng:

```text
127.0.0.1 lozido.local
```

Sau đó truy cập:

```text
http://lozido.local
```

Nếu không cấu hình hosts, dùng:

```text
http://localhost
```

## 12. Cài Source App Laravel/PHP

Vào container PHP:

```powershell
docker compose exec php-fpm bash
```

Vào thư mục app:

```bash
cd /var/www/<APP_DIR>
```

Cài dependency:

```bash
composer install
```

Tạo file `.env` app nếu chưa có:

```bash
cp .env.example .env
```

Mở file `.env` của app từ Windows tại:

```text
<WORKSPACE>\<APP_DIR>\.env
```

Cấu hình database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=lozido_renter_db_name
DB_USERNAME=lozido_renter_user_name
DB_PASSWORD=strong_password
```

Chạy lệnh Laravel:

```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Nếu app có frontend:

```bash
npm install
npm run dev
```

Thoát container:

```bash
exit
```

Mở trình duyệt:

```text
http://localhost
```

hoặc:

```text
http://lozido.local
```

## 13. Kết Nối MySQL Từ Windows

Từ app trong container:

```text
Host: mysql
Port: 3306
```

Từ phần mềm trên Windows như DBeaver, TablePlus, Navicat:

```text
Host: 127.0.0.1
Port: 33066
User: root
Password: giá trị DB_ROOT_PASSWORD trong .env
```

Hoặc user/password bạn tạo trong `mysql\init\initialization.sql`.

Vào MySQL từ container:

```powershell
docker compose exec mysql mysql -uroot -p
```

## 14. Các Lệnh Quản Trị Thường Dùng

Start:

```powershell
docker compose up -d mysql php-fpm nginx
```

Stop:

```powershell
docker compose down
```

Restart:

```powershell
docker compose restart
```

Rebuild:

```powershell
docker compose up -d --build --force-recreate mysql php-fpm nginx
```

Rebuild một service:

```powershell
docker compose up -d --build --force-recreate --no-deps php-fpm
docker compose up -d --build --force-recreate --no-deps nginx
```

Vào container:

```powershell
docker compose exec php-fpm bash
docker compose exec nginx bash
docker compose exec mysql bash
```

Xem log:

```powershell
docker compose logs -f nginx
docker compose logs -f php-fpm
docker compose logs -f mysql
```

## 15. Backup Và Restore MySQL Trên Windows

Tạo thư mục backup:

```powershell
New-Item -ItemType Directory -Force mysql_backup
```

Backup:

```powershell
docker compose exec mysql sh -c "exec mysqldump -uroot -p'lozido_dev_password' lozido_renter_db_name" > mysql_backup\lozido_renter_db_name.sql
```

Restore:

```powershell
Get-Content mysql_backup\lozido_renter_db_name.sql | docker compose exec -T mysql sh -c "exec mysql -uroot -p'lozido_dev_password' lozido_renter_db_name"
```

Đổi `lozido_dev_password` và `lozido_renter_db_name` theo cấu hình thật.

## 16. SSL Và Certbot Trên Windows

Khi chạy local trên Windows, không cần Certbot. Dùng HTTP:

```text
http://localhost
```

Chỉ dùng Certbot khi:

- Máy Windows/server có IP public.
- Domain thật đã trỏ DNS về IP đó.
- Port `80` và `443` mở từ Internet vào máy.

Khi đó sửa `docker-compose.yml`, service `certbot`, đổi domain/email:

```yaml
certbot:
  image: certbot/certbot:latest
  command: certonly --webroot --cert-name lozido-0009 -w /var/www/docker-lozido/webroot -d 'your-domain.com' --email your_email@example.com --agree-tos --force-renewal --no-eff-email --expand
```

Sau đó chạy:

```powershell
docker compose run --...
