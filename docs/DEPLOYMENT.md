# Commerce Master Framework — 部署上线指南

## 前提条件

| 项目 | 要求 |
|------|------|
| 服务器 | VPS/云服务器，Ubuntu 20.04+，最少 2GB RAM |
| 权限 | root 或 sudo 用户 |
| 域名 | 已购买，DNS 可配置 |
| GitHub | 仓库 `dreamnestlify/commerce-master-framework` 的访问权限 |

---

## 部署架构

```
Internet → :80/:443 (Caddy 自动 HTTPS)
                ↓
          WordPress 容器 (PHP 8.3 + Apache)
                ↓
          MariaDB 11.8 容器 (内部网络)
```

- **Caddy** — 反向代理，自动申请/续期 Let's Encrypt SSL 证书
- **WordPress** — 仅监听内部端口，不对外暴露
- **MariaDB** — 仅内部网络，不对外暴露端口
- **phpMyAdmin** — 默认关闭，需要时通过 SSH 隧道访问

---

## 步骤 1：配置 DNS

在你的域名服务商后台，添加 A 记录：

```
类型: A
主机: @ (或留空)
值:   <你的服务器IP>
TTL:  600

类型: A
主机: www
值:   <你的服务器IP>
TTL:  600
```

验证 DNS 生效（等待 5-30 分钟）：

```bash
dig +short your-domain.com
# 应返回你的服务器 IP
```

---

## 步骤 2：服务器安装 Docker

SSH 登录服务器后执行：

```bash
# 更新系统
sudo apt update && sudo apt upgrade -y

# 安装 Docker
curl -fsSL https://get.docker.com | sudo sh

# 将当前用户加入 docker 组（免 sudo）
sudo usermod -aG docker $USER

# 重新登录使组生效
exit
# 重新 SSH 登录

# 验证
docker --version
docker compose version
```

---

## 步骤 3：获取代码

```bash
# 安装 git（如果没有）
sudo apt install -y git

# 克隆仓库
cd /opt
sudo git clone https://github.com/dreamnestlify/commerce-master-framework.git commerce-master
cd commerce-master
sudo chown -R $USER:$USER .
```

---

## 步骤 4：配置生产环境

```bash
# 复制生产环境模板
cp .env.prod.example .env

# 生成 WordPress 安全密钥（8个）
for i in $(seq 1 8); do openssl rand -hex 32; done
# 将输出的 8 个值依次填入 .env 的 AUTH_KEY ... NONCE_SALT

# 生成数据库密码
openssl rand -hex 16  # 用于 DB_PASSWORD
openssl rand -hex 16  # 用于 DB_ROOT_PASSWORD

# 编辑 .env，填入所有真实值
nano .env
```

### .env 必须修改的字段：

```ini
# ⚠️ 这些字段不改完，init.sh 会拒绝运行
DOMAIN=your-real-domain.com
WP_HOME=https://your-real-domain.com
WP_SITEURL=https://your-real-domain.com

DB_PASSWORD=<上面生成的密码>
DB_ROOT_PASSWORD=<上面生成的密码>

ADMIN_PASSWORD=<至少12位强密码>
ADMIN_EMAIL=your-real-email@example.com

AUTH_KEY=<openssl生成的值1>
SECURE_AUTH_KEY=<openssl生成的值2>
LOGGED_IN_KEY=<openssl生成的值3>
NONCE_KEY=<openssl生成的值4>
AUTH_SALT=<openssl生成的值5>
SECURE_AUTH_SALT=<openssl生成的值6>
LOGGED_IN_SALT=<openssl生成的值7>
NONCE_SALT=<openssl生成的值8>

BRAND_NAME=你的品牌名
SUPPORT_EMAIL=support@your-real-domain.com
```

---

## 步骤 5：启动服务

```bash
# 使用生产配置启动（Caddy + WordPress + MariaDB）
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# 查看容器状态
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps
```

预期输出：

```
NAME                       STATUS              PORTS
commerce_master_caddy      Up                  0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
commerce_master_wp         Up (healthy)
commerce_master_db         Up (healthy)
```

> Caddy 首次启动会自动向 Let's Encrypt 申请 SSL 证书（需要 DNS 已生效）。
> 如果证书申请失败，检查 DNS 是否已指向本服务器。
> WordPress 容器首次启动会自动运行 `composer install` 安装 Stripe SDK，耗时约 1-2 分钟。

---

## 步骤 6：初始化 WordPress

```bash
# 运行初始化脚本（安装 WordPress、WooCommerce、导入演示数据）
docker compose -f docker-compose.yml -f docker-compose.prod.yml --profile cli run --rm wpcli bash /scripts/init.sh
```

脚本会自动完成：
- 安装 WordPress（使用 .env 中的管理员账号）
- 安装并激活 WooCommerce 11.0.0
- 激活 commerce-core 插件
- 激活 commerce-block-theme 主题
- 创建所有页面（Home, Shop, Cart, Checkout, My Account, Wishlist, Terms, Privacy）
- 导入 16 个演示商品（含变体、分类、标签）
- 运行 smoke check 验证

---

## 步骤 7：验证网站

```bash
# 检查首页是否可访问
curl -I https://your-domain.com
# 应返回 200 OK

# 检查 admin 后台
curl -I https://your-domain.com/wp-admin
# 应返回 200 OK 或 302 重定向
```

浏览器访问：
- **首页**: https://your-domain.com
- **商店**: https://your-domain.com/shop
- **后台**: https://your-domain.com/wp-admin

---

## 步骤 8（可选）：配置 SMTP

不配置 SMTP，WordPress 无法发送订单确认邮件和密码重置邮件。

推荐使用免费的 SMTP 服务：
- **Resend** (https://resend.com) — 每月 3000 封免费
- **Brevo** (https://brevo.com) — 每天 300 封免费
- **Amazon SES** — 每月 62000 封免费（EC2 用户）

获取 SMTP 凭据后，在 `.env` 中填入：

```ini
SMTP_HOST=smtp.resend.com
SMTP_PORT=587
SMTP_USER=resend
SMTP_PASSWORD=re_xxxxxxxx
SMTP_FROM_EMAIL=noreply@your-domain.com
SMTP_FROM_NAME=Your Brand Name
```

然后安装 WP Mail SMTP 插件配置 WordPress 使用 SMTP。

---

## 常用运维命令

```bash
# 查看实时日志
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f

# 仅看 WordPress 日志
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs -f wordpress

# 重启服务
docker compose -f docker-compose.yml -f docker-compose.prod.yml restart

# 停止服务
docker compose -f docker-compose.yml -f docker-compose.prod.yml down

# 更新代码后重新部署
git pull origin main
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
# 如果有数据库变更，重新运行 init
docker compose -f docker-compose.yml -f docker-compose.prod.yml --profile cli run --rm wpcli bash /scripts/init.sh

# 备份数据库
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec db mysqldump -u root -p<DB_ROOT_PASSWORD> wordpress > backup_$(date +%Y%m%d).sql

# 恢复数据库
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec -T db mysql -u root -p<DB_ROOT_PASSWORD> wordpress < backup_20260809.sql

# 临时开启 phpMyAdmin（仅 SSH 隧道访问）
docker compose -f docker-compose.yml -f docker-compose.prod.yml --profile pma up -d phpmyadmin
# 然后在本地终端建立隧道：
# ssh -L 8090:localhost:8090 user@your-server-ip
# 浏览器访问 http://localhost:8090
```

---

## 安全检查清单

- [ ] `.env` 文件权限设为 600：`chmod 600 .env`
- [ ] 服务器防火墙仅开放 22, 80, 443 端口
- [ ] WordPress 管理员密码为强密码（≥12 字符）
- [ ] 数据库密码与管理员密码不同
- [ ] WP_DEBUG 已关闭（生产配置自动关闭）
- [ ] phpMyAdmin 默认不启动
- [ ] MariaDB 端口不对外暴露（生产配置自动关闭）
- [ ] SSL 证书有效（Caddy 自动管理）
- [ ] 定期备份数据库（建议设置 cron）

---

## 故障排查

### 网站无法访问

```bash
# 1. 检查容器是否运行
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps

# 2. 检查 Caddy 日志
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs caddy

# 3. 检查 WordPress 日志
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs wordpress

# 4. 检查 DNS
dig +short your-domain.com  # 应返回服务器 IP

# 5. 检查防火墙
sudo ufw status
# 确保 80 和 443 端口开放
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### SSL 证书申请失败

```bash
# Caddy 日志会显示具体错误
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs caddy | grep -i error

# 常见原因：
# 1. DNS 未指向本服务器 → 等待 DNS 生效
# 2. 80 端口被占用 → sudo lsof -i :80
# 3. Let's Encrypt 限速 → 等待 1 小时后重试
# 4. 域名使用 Cloudflare 等 CDN 代理 → 临时关闭代理（DNS-only，灰色云）
#    等 Caddy 申请到证书后再开启
```

### DNS 解析到多个 IP

如果 `nslookup your-domain.com` 返回多个 IP，说明域名可能配置了：
- 多条 A 记录（删除多余的，只保留服务器 IP）
- CDN 代理（如 Cloudflare 橙色云）

如果使用了 Cloudflare：
1. 先登录 Cloudflare，把域名记录改成 **DNS-only（灰色云）**
2. 等 5 分钟，确认 `nslookup` 只返回服务器 IP
3. 重新启动 Caddy 申请证书：`docker compose -f docker-compose.yml -f docker-compose.prod.yml restart caddy`
4. 证书申请成功后，可以重新开启 Cloudflare 代理

### Smoke check 报 "Stripe PHP SDK is missing"

```bash
# WordPress 容器启动时会自动运行 composer install 安装 Stripe SDK
# 如果仍然缺失，检查 WordPress 容器日志
docker compose -f docker-compose.yml -f docker-compose.prod.yml logs wordpress | tail -50

# 手动进入容器安装
docker compose -f docker-compose.yml -f docker-compose.prod.yml exec wordpress bash
cd /var/www/html/wp-content/plugins/commerce-core
composer install --no-dev --no-interaction --prefer-dist
exit
```

### init.sh 失败

```bash
# 重新运行（init.sh 是幂等的，安全重跑）
docker compose -f docker-compose.yml -f docker-compose.prod.yml --profile cli run --rm wpcli bash /scripts/init.sh

# 如果报 "password not set" → 检查 .env 中的密码和密钥
# 如果报 "database connection" → 检查 db 容器是否健康
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps db
```
