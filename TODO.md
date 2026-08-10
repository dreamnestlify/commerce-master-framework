# Zalandy 独立站待办清单

> 最后检查时间: 2026-08-10 06:00 (北京时间)
> 当前状态: 31 商品 | 9 分类 | 18 页面 | 4 mu-plugins | Woostify 2.5.4 | WP 7.0.3 | WC 11.0.0

---

## 🔴 P0 — 必须修复 (上线前必做)

### 1. 15个时尚商品无图片
- 16个珠宝商品有图片 ✅
- 15个时尚商品全部使用 WooCommerce 占位图 ❌
- **你需要做的:** 准备 15 张商品图 (建议 800×1000px 白底图)，在后台 商品 → 全部商品 逐个上传
- 商品清单: Silk Wrap Dress, Cotton Linen Jumpsuit, Oversized Cardigan, Wide Leg Trousers, Pleated Mini Skirt, Cropped Blazer, Cotton T-Shirt, Oxford Shirt, Chino Pants, Wool Overcoat, Cargo Pants, Leather Belt, Cashmere Scarf, Crossbody Bag, Retro Sunglasses

### 2. 店铺地址/国家配置错误
- 当前: 地址="Zalandy Jewelry", 城市="Shanghai", 国家=CN ❌
- 正确: 公司是 Equi international UG (德国), 地址应为德国 Schneverdingen
- **你需要做的:** 后台 → WooCommerce → 设置 → 常规 → 修改地址/城市/邮编/国家为 DE

### 3. 货币设置错误
- 当前: USD (美元) ❌
- 应改为: EUR (欧元) — 德国公司卖欧洲市场
- **你需要做的:** 后台 → WooCommerce → 设置 → 常规 → 货币改为 欧元 (€)

### 4. 税务计算未开启
- 当前: woocommerce_calc_taxes = no ❌
- 欧盟销售必须收 VAT (19% 德国标准税率)
- **你需要做的:** 后台 → WooCommerce → 设置 → 税费 → 勾选"启用税费" → 配置德国 19% VAT 税率

### 5. 无配送区域设置
- 当前: 0 个 shipping zone ❌
- 顾客无法看到运费
- **你需要做:** 后台 → WooCommerce → 设置 → 配送 → 添加配送区域 (德国/欧盟/国际) + 设置运费

### 6. 无 SMTP 邮件配置
- 当前: 无任何 SMTP 插件 ❌
- WooCommerce 订单邮件、密码重置邮件都不会发出
- **你需要做的:** 安装 WP Mail SMTP 插件 + 配置 Resend/SMTP 服务商 → 用 buysing@icloud.com 或新邮箱

### 7. Stripe / PayPal API 密钥
- Stripe 和 PayPal 网关已启用 ✅ (自定义网关)
- 但 API 密钥是否已配置需确认
- **你需要做的:** 后台 → WooCommerce → 设置 → 支付 → 检查 Stripe/PayPal 的密钥是否填入
  - Stripe: 需要 pk_live_xxx / sk_live_xxx (或 test mode 密钥先测)
  - PayPal: 需要 Client ID / Secret

### 8. Contact 页面链接问题
- 页面 slug 是 /contact/ 不是 /contact-us/ ⚠️
- 导航菜单中的链接需检查是否正确

---

## 🟡 P1 — 重要优化 (强烈建议)

### 9. 安装缓存插件
- 当前无缓存 ❌ → 网站速度慢
- 建议: WP Super Cache 或 W3 Total Cache (免费)
- **操作:** 后台 → 插件 → 安装 → 搜索 "WP Super Cache"

### 10. 安装 SEO 插件
- 当前无 SEO 插件 ❌ → 搜索引擎收录差
- 建议: Yoast SEO 或 Rank Math (免费)
- **操作:** 后台 → 插件 → 安装 → 搜索 "Rank Math SEO"

### 11. 配置 Google Analytics 4 / Meta Pixel
- 当前无分析追踪 ❌ → 无法追踪转化数据
- **你需要做的:**
  - 注册 GA4 账号 → 获取 Measurement ID (G-XXXXXX)
  - 注册 Meta Business → 获取 Pixel ID
  - 后台 → WooCommerce → 设置 → 集成 → 填入 ID

### 12. 邮件发件人名称为空
- woocommerce_email_from_name 为空 ❌
- **操作:** 后台 → WooCommerce → 设置 → 邮件 → 发件人名称填 "Zalandy"

### 13. 页脚社交媒体链接
- 当前页脚无社媒链接 ❌
- **你需要做:** 准备 Instagram / Facebook / TikTok / Pinterest 账号链接

### 14. 清理草稿页面
- "Refund and Returns Policy" (ID 9, 草稿) — 与已发布的 "Return & Refund Policy" 重复
- **操作:** 后台 → 页面 → 删除草稿状态的重复页面

### 15. 清理 Uncategorized 分类
- "Uncategorized" 分类 0 个商品 ❌
- **操作:** 后台 → 商品 → 分类 → 删除 (需先确认无商品关联)

### 16. 服装尺码指南
- 当前尺码指南只涵盖珠宝 ❌
- **操作:** 添加服装尺码对照表 (XS-XXL, 国际码对照)

### 17. 开启用户注册
- users_can_register 未设置 ❌
- WooCommerce 需要用户注册才能"我的账户"功能正常
- **操作:** 后台 → 设置 → 常规 → 成员资格 → 勾选"任何人都可以注册"

---

## 🟢 P2 — 锦上添花 (后续迭代)

### 18. 网站安全加固
- 安装 Wordfence Security 或 Sucuri (免费防火墙+恶意软件扫描)
- 配置登录限制 (2FA)

### 19. 自动备份方案
- 当前无备份 ❌
- 建议: UpdraftPlus (免费) → 每日自动备份到 Google Drive/Dropbox

### 20. XML Sitemap
- 安装 SEO 插件后自动生成 (见 #10)
- 提交到 Google Search Console + Bing Webmaster Tools

### 21. 商品评论/评分
- 当前商品无评论 ❌
- 可后续导入种子评论 或 等真实顾客评价

### 22. 博客/内容营销
- 当前无博客页面 ❌
- 时尚搭配、珠宝保养等文章有助于 SEO

### 23. Favicon 自定义
- 当前使用默认 WordPress 图标 ❌
- 准备 Zalandy logo 图标 (512×512 PNG)

### 24. 库存管理
- 当前商品未设置库存数量 ❌
- 建议每个商品设置库存 + 启用缺货通知

### 25. 商品变体完善
- 服装商品需要颜色/尺码变体 (S/M/L, 不同颜色)
- 当前时尚商品为简单商品 ❌

### 26. 多语言支持 (可选)
- 当前前台全英文 + 后台中文
- 如需德语前台 → 安装 WPML 或 Polylang

### 27. 退款/退货流程自动化
- 配合 Packlink Pro Shipping 插件生成退货标签
- 配置 WooCommerce 退货申请表单

---

## ✅ 已完成清单

- [x] WordPress 7.0.3 + WooCommerce 11.0.0 安装
- [x] Woostify 主题激活
- [x] SSL 证书 (Really Simple SSL)
- [x] 31 个商品 (16 珠宝 + 15 时尚)
- [x] 9 个商品分类 (5 珠宝 + 4 时尚)
- [x] 18 个页面 (合规页面/Shop/Cart/Checkout/My Account/Wishlist)
- [x] 自定义页脚 ( zalandy-footer.php mu-plugin)
- [x] 自定义后台 UI (zalandy-admin-ui.php mu-plugin)
- [x] 自定义字体 Inter + Playfair Display (zalandy-fonts.php mu-plugin)
- [x] Cookie 同意横幅 (zalandy-cookie-consent.php mu-plugin)
- [x] 后台中文化 (zh_CN 语言包)
- [x] 导航菜单 (主菜单 + 页脚菜单)
- [x] Wishlist 心愿单功能
- [x] 固定链接结构 (/%postname%/)
- [x] Jetpack (已禁用 Protect 模块)
- [x] Complianz GDPR 合规插件
- [x] Klaviyo 邮件营销插件
- [x] Packlink Pro 物流插件
- [x] Printful 按需打印插件
- [x] Google Listings & Ads
- [x] Pinterest/Snapchat/Reddit for WooCommerce
- [x] SSH 密钥认证 + fail2ban 已关闭
- [x] 自定义 Stripe + PayPal 支付网关 (commerce-core 插件)
- [x] 自动草稿商品已清理 (ID 291 已删除)
