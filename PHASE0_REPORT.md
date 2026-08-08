# Phase 0 — Engineering Master Verification Report (Code Review Fix Round)

**项目：** Commerce Master — WordPress + WooCommerce Fashion E-Commerce Framework  
**版本：** 0.1.0  
**日期：** 2026-08-08  
**执行者：** WorkBuddy AI Assistant  
**状态：** 第二轮修复完成，等待再次审核

---

## 0. 修复轮次摘要

本次为第二轮代码审查修复。针对审查发现的 6 类问题，逐项完成修复：

| 类别 | 问题数 | 修复状态 |
|---|---|---|
| 一、WooCommerce 区块主题 | 区块名称、模板结构、Cart/Checkout | ✅ 全部修复 |
| 二、Docker/环境配置 | 环境变量、密码校验、错误抑制 | ✅ 全部修复 |
| 三、测试与静态分析 | autoload、stub、测试质量、PHPStan | ✅ 全部修复 |
| 四、演示数据初始化 | taxonomy、错误检查、幂等、PNG | ✅ 全部修复 |
| 五、依赖可重复性 | lock 文件、CI、README | ✅ 全部修复 |
| 六、自动检查 | 区块扫描、smoke check、文档诚实化 | ✅ 全部新增 |

---

## 1. 实现结果概述

Phase 0 工程母版已全部完成并经过一轮代码审查修复。覆盖以下全部 Phase 0 交付物：

| 交付物 | 状态 |
|---|---|
| Docker Compose 开发环境 | ✅ 配置完成（含全部环境变量传递、密码校验） |
| commerce-core 插件骨架 | ✅ 完整实现（含可注入 Logger、22 个单元测试） |
| commerce-block-theme 主题骨架 | ✅ 完整实现（WC 11 区块名称全部修正） |
| theme.json 设计令牌 + Style Variations | ✅ 3 套风格 |
| 演示商品数据 + 可收敛幂等初始化脚本 | ✅ 10 个商品（含错误检查、验证） |
| 编码规范 + 自动化检查配置 | ✅ 配置完成 |
| 基础测试 | ✅ 22 个单元测试已编写（**未执行**——无 PHP 运行环境） |
| 区块名称扫描器 | ✅ 新增 `scripts/block-name-scanner.sh`（已运行通过） |
| 初始化后 Smoke Check | ✅ 新增 `scripts/smoke-check.sh`（需 Docker 环境运行） |
| 依赖锁定 | ✅ `package-lock.json` 已生成（102KB）；`composer.lock` **无法生成**（本机无 PHP/Composer） |
| 文档（README/ARCHITECTURE/ROADMAP/DECISIONS） | ✅ 完整且诚实化 |

---

## 2. 版本选择依据

以下版本经 web 搜索验证，基于 2026年8月8日的官方发布记录：

| 组件 | 选用版本 | 发布日期 | 选择依据 |
|---|---|---|---|
| WordPress | **7.0.2** | 2026-07-17 | 当前 7.0 系列最新安全更新版本。7.0 于 2026-05-20 发布，7.0.1 于 2026-07-09 发布，7.0.2 于 2026-07-17 发布（修复 SQL 注入和 REST API RCE 漏洞）。不使用 7.1 Beta/RC（预计 2026-08-19 发布正式版）。旧版 6.7（2024-11 发布）已严重过时。 |
| WooCommerce | **11.0.0** | 2026-08-04 | 当前最新稳定版。551 个 PR、89 位贡献者。含性能优化、访客订单认领、分析可靠性改进。要求 WordPress 6.9+，与 WP 7.0.2 兼容。init.sh 中锁定版本号 `--version=11.0.0`。 |
| WP-CLI | **2.12.0** | 2026（当前稳定版） | WP-CLI 官网显示当前稳定版为 2.12.0。Docker 镜像标签 `wordpress:cli-2.12.0-php8.3`。 |
| MariaDB | **11.8 LTS** | 2026-05-27 (11.8.8) | MariaDB 11.6 已 EOL（社区支持于 2025-02 结束）。11.8 是长期支持版，社区维护至 2028-06。12.3 LTS 虽于 2026-05-28 发布，但过于新（仅 2 个月），选择更成熟的 11.8 LTS。 |
| PHP | **8.3** | — | PHP 8.3 安全支持至 2027-12。WordPress 7.0.2 和 WooCommerce 11.0 均要求 PHP 8.1+。8.3 是稳定且广泛支持的版本。 |
| Node.js | **22** | LTS 至 2027-04 | Node 22 是维护期 LTS。Node 24 也是 LTS（2025-10 起），但 22 更成熟。开发环境工具链兼容。 |
| phpMyAdmin | **5.2** | — | 开发便利工具，非生产组件。5.2 是当前稳定版。 |

### 版本兼容性矩阵

```
WordPress 7.0.2  ← 要求 PHP ≥ 8.1     ← PHP 8.3 ✅
WooCommerce 11.0 ← 要求 WordPress ≥ 6.9 ← WP 7.0.2 ✅
WooCommerce 11.0 ← 要求 PHP ≥ 8.1     ← PHP 8.3 ✅
WP-CLI 2.12.0    ← 要求 WordPress ≥ 3.7 ← WP 7.0.2 ✅
MariaDB 11.8 LTS ← WordPress 兼容      ← ✅
theme.json v3    ← 要求 WordPress ≥ 6.5 ← WP 7.0.2 ✅
```

---

## 3. 第二轮修复详情

### 一、修复 WooCommerce 区块主题

| 问题 | 修复内容 |
|---|---|
| 区块名称不符合 WC 11 | `woocommerce/breadcrumb` → `woocommerce/breadcrumbs`；`woocommerce/result-count` → `woocommerce/product-results-count`；`woocommerce/catalog-ordering` → `woocommerce/catalog-sorting`；`woocommerce/product-short-description` → `woocommerce/product-summary`；`woocommerce/add-to-cart` → `woocommerce/add-to-cart-form`；`woocommerce/product-tabs` → `woocommerce/product-details` |
| header 客户/购物车区块名称错误 | `woocommerce-customer-account` → `woocommerce/customer-account`（斜杠命名空间，非连字符）；`woocommerce-mini-cart` → `woocommerce/mini-cart` |
| Cart/Checkout 未使用 page-content-wrapper | 所有 WC 页面模板添加 `woocommerce/page-content-wrapper` 包装器 |
| Cart/Checkout 使用短代码而非区块 | `init.sh` 中页面内容从 `[woocommerce_cart]` / `[woocommerce_checkout]` 改为 `<!-- wp:woocommerce/cart /-->` / `<!-- wp:woocommerce/checkout /-->`，含收敛式短代码→区块迁移 |
| Checkout 模板重复 header | 移除 `page-checkout.html` 底部重复的 `checkout-header` |
| 商品归档不继承查询 | `inherit:false` → `inherit:true` |
| 商品筛选区块 | 旧 `woocommerce/stock-filter` 独立区块 → `woocommerce/product-filters` 含内层筛选子区块 |
| 无结果提示 | 添加 `woocommerce/product-collection-no-results` 区块 |
| 通知区块 | 所有 WC 页面添加 `woocommerce/store-notices` |

**验证：** `scripts/block-name-scanner.sh` 扫描 25 个文件，0 个废弃名称。✅

### 二、修复 Docker/环境配置

| 问题 | 修复内容 |
|---|---|
| 环境变量未传递到服务 | `wordpress` 和 `wpcli` 服务均添加 `ADMIN_USER`、`ADMIN_PASSWORD`、`ADMIN_EMAIL`、`BRAND_NAME`、`BRAND_TAGLINE`、`DEFAULT_LOCALE`、`BASE_CURRENCY`、`ENABLED_CURRENCIES`、`DEFAULT_MARKET`、`SUPPORT_EMAIL`、`SUPPORT_PHONE`、`WP_HOME`（wpcli） |
| 缺少管理员凭据环境变量 | `.env.example` 新增 `ADMIN_USER`、`ADMIN_PASSWORD`、`ADMIN_EMAIL` 配置段 |
| 密码静默回退到弱值 | `init.sh` 添加 `check_password()` 函数：拒绝空密码、禁止列表密码（admin/password/change_me 等）、拒绝 <12 字符密码。`set -euo pipefail` 确保失败即终止 |
| Salt 占位符未校验 | `.env.example` 中 salt 值改为 `generate_me_replace_with_real_value`；`init.sh` 添加 salt 校验拒绝 `generate_me*` 模式 |
| 关键步骤错误被抑制 | 移除以下步骤的 `2>/dev/null \|\| true`：WooCommerce 安装/激活、commerce-core 激活、主题激活、固定链接设置、WooCommerce 设置、演示数据导入。仅非关键清理（删除示例文章/评论）保留 |
| 数据库密码占位符未校验 | `init.sh` 添加 `DB_PASSWORD` 校验拒绝 `change_me` / `your_password` / 空值 |

### 三、修复测试与静态分析

| 问题 | 修复内容 |
|---|---|
| autoload 路径错误 | `bootstrap.php` 中 `__DIR__ . '/../../../vendor/autoload.php'`（3 级向上到 `wp-content/plugins/vendor/`）改为 `dirname(__DIR__, 2) . '/vendor/autoload.php'`（2 级到 `commerce-core/vendor/`） |
| 缺少 WP/WC 函数 stub | 添加 `wp_json_encode`、`wp_kses_post`、`do_action`、`apply_filters`、`rest_ensure_response`、`register_rest_route`、`is_wp_error`、`wc_get_logger`、`get_terms`、`wp_insert_term`、`term_exists`、`sanitize_title`、`get_term` 等 stub |
| 缺少 $wpdb stub | 添加 `$wpdb` stub（`prepare`、`get_var`、`esc_like`、`insert` 方法模拟文章/属性查询），含 `_test_add_post()` 和 `_test_add_attribute()` 辅助函数 |
| 缺少 WP_Error 类 | 添加 `WP_Error` 类 stub |
| Idempotency 仅测试"不存在"路径 | 从 3 个测试扩展到 10 个：新增 6 个"已存在"路径测试（`find_post_by_title_returns_id_when_found`、`find_post_by_title_does_not_match_different_type`、`find_product_by_name_returns_id_when_found`、`find_term_by_name_returns_id_when_found`、`find_attribute_by_slug_returns_id_when_found`、`test_repeated_find_returns_same_id`） |
| Logger 测试用 assertTrue(true) | 重构 Logger 为可注入 handler 模式（构造函数接受 `?callable $handler`）；`sanitize_data()` 改为 public；12 个测试全部使用真实断言（验证敏感值不在输出中且 `[REDACTED]` 在输出中） |
| PHPStan 配置无效引用 | 移除 `phar://phpstan.phar/conf/wordpress-baseline.neon`（Composer 安装时无效）；保留有效的 `vendor/szepeviktor/phpstan-wordpress/extension.neon` |
| PHPCS minimum_wp_version 过旧 | `phpcs.xml.dist` 中 `minimum_wp_version` 从 "6.5" 改为 "7.0" |
| composer lint 范围不全 | `find src` 改为 `find src commerce-core.php uninstall.php tests` |
| composer phpcs 路径冗余 | `phpcs --standard=phpcs.xml.dist src/ tests/` 改为 `phpcs --standard=phpcs.xml.dist`（标准文件已包含路径） |

**测试总数：** 10 个 Idempotency + 12 个 Logger = **22 个单元测试**（均为已编写、已配置，**未执行**——无 PHP/Composer）。

### 四、修复演示数据初始化

| 问题 | 修复内容 |
|---|---|
| taxonomy 未在执行上下文注册 | `wc_create_attribute()` 后添加 `register_taxonomy('pa_' . $slug, ...)` 在当前执行上下文注册 |
| 缺少错误检查 | 全局添加 `_assert_not_wp_error()` 和 `_assert_product_saved()` 辅助函数；`wc_create_attribute`、`wp_insert_term`、`wc_get_product`、`wc_save_product` 均检查返回值 |
| 幂等仅"跳过已存在" | 改为可收敛幂等：已存在的分类描述通过 `wp_update_term` 更新；已存在的产品完整修正（名称、状态、SKU、描述、价格、库存、重量、尺寸、分类、图片、属性）；产品类型不匹配时检测并重建 |
| 变体幂等不完整 | 已存在的变体更新（价格、库存、图片）；缺失的变体创建 |
| PNG 回退生成无效图片 | 移除手动 PNG 字节构造（产生无效文件）；改为要求 GD 扩展，不可用时明确警告并跳过图片生成 |
| 无运行后验证 | 新增 post-run 验证：检查产品数量、分类、属性、terms、每个产品的变体数量 |

### 五、保证依赖可重复

| 问题 | 修复内容 |
|---|---|
| `.gitignore` 忽略 lock 文件 | 移除 `/composer.lock` 行；lock 文件注释为"committed for reproducible builds" |
| CI 使用非确定性安装 | `npm ci \|\| npm install` 改为仅 `npm ci`（不允许 fallback） |
| README 项目结构树有误 | 移除根目录 `composer.json` / `package.json` 条目（实际不存在）；在正确位置（`wp-content/plugins/commerce-core/` 和 `wp-content/themes/commerce-block-theme/`）添加 |
| README 使用 `npm install` | 改为 `npm ci`（确定性安装） |
| `package-lock.json` 缺失 | ✅ 已生成（102KB，2465 行，189 包，0 漏洞） |
| `composer.lock` 缺失 | ⚠️ **无法生成**——本机无 PHP/Composer。CI 环境中 `composer install` 将自动生成。建议首次 CI 运行后提交生成的 `composer.lock` |
| README 缺少新脚本 | 项目结构树新增 `block-name-scanner.sh`、`smoke-check.sh`、`demo-data.php` |

### 六、增加自动检查

| 新增项 | 文件 | 说明 |
|---|---|---|
| 区块名称扫描器 | `scripts/block-name-scanner.sh` | 扫描主题 templates/parts/patterns 中的废弃 WC 区块名称和短代码，精确匹配（避免子串误报） |
| 初始化后 Smoke Check | `scripts/smoke-check.sh` | 验证 WP 已安装、WC 已激活、主题已激活、必需页面存在、Cart/Checkout 使用区块（非短代码）、固定链接、产品数量、分类、属性、站点 URL |
| WCAG 声明诚实化 | `style.css`、`assets/css/theme.css` | "WCAG 2.2 AA compliant" → "WCAG 2.2 AA target (not yet audited)" |

---

## 4. 实际运行的检查及结果

### ✅ 已运行并通过的检查

| # | 检查项 | 工具 | 结果 |
|---|---|---|---|
| 1 | YAML 配置验证 | js-yaml (Node 22) | ✅ docker-compose.yml + ci.yml 有效 |
| 2 | JSON 配置验证 | JSON.parse (Node 22) | ✅ 6 个 JSON 文件全部有效（package.json、theme.json、3×style variations、composer.json） |
| 3 | ESLint (JavaScript) | ESLint 9 (via npm run lint:js) | ✅ 0 errors, 0 warnings |
| 4 | Stylelint (CSS) | Stylelint 16 (via npm run lint:css) | ✅ 0 errors, 0 warnings |
| 5 | 区块名称扫描 | block-name-scanner.sh | ✅ 25 个文件，0 个废弃名称 |
| 6 | Git 密钥检查 | git ls-files + grep | ✅ 仅占位符/安全代码/配置模板，无真实密钥 |
| 7 | .env 排除检查 | git ls-files | ✅ .env 未被追踪 |
| 8 | 数据库/上传排除检查 | git ls-files + grep | ✅ 无数据库文件或上传目录提交 |
| 9 | package-lock.json 生成 | npm install --package-lock-only (Node 22) | ✅ 189 包，0 漏洞 |

### ⚠️ 已配置但未执行的检查

以下检查的配置文件已就绪，但因本机未安装 PHP/Composer/Docker，**实际未执行**。不声称通过。

| # | 检查项 | 配置文件 | 未执行原因 | 安装后执行命令 |
|---|---|---|---|---|
| 1 | PHP 语法 Lint | composer.json `lint` 脚本 | PHP 未安装 | `composer lint` |
| 2 | PHPCS (WPCS) | phpcs.xml.dist | PHP/Composer 未安装 | `composer phpcs` |
| 3 | PHPStan | phpstan.neon | PHP/Composer 未安装 | `composer phpstan` |
| 4 | PHPUnit (22 个测试) | phpunit.xml.dist | PHP/Composer 未安装 | `composer test` |
| 5 | Composer validate | composer.json | Composer 未安装 | `composer validate --strict` |
| 6 | composer.lock 生成 | composer.json | Composer 未安装 | `composer install`（CI 首次运行时生成） |
| 7 | Docker Compose 启动 | docker-compose.yml | Docker 未安装 | `docker compose up -d` |
| 8 | WordPress 安装验证 | init.sh | 依赖 Docker | `docker compose --profile cli run --rm wpcli bash /scripts/init.sh` |
| 9 | 初始化后 Smoke Check | smoke-check.sh | 依赖 Docker | `docker compose --profile cli run --rm wpcli bash /scripts/smoke-check.sh` |
| 10 | 初始化脚本幂等性 | init.sh + demo-data.php | 依赖 Docker | 运行两次验证 |

---

## 5. 目录结构

```
.
├── .env.example                    # 环境配置模板（含管理员凭据、密码校验占位符）
├── .gitignore                       # 排除 DB/uploads/secrets（lock 文件已提交）
├── .github/workflows/ci.yml         # CI 流水线（确定性 npm ci）
├── AGENTS.md                        # 开发代理指南
├── README.md                        # 项目说明（修正项目结构树）
├── PHASE0_REPORT.md                 # 本报告
├── docker-compose.yml               # WordPress 7.0.2 + MariaDB 11.8 + phpMyAdmin + WP-CLI 2.12.0 + Node 22
├── docs/
│   ├── ARCHITECTURE.md              # 6 层架构 + 分层图
│   ├── DECISIONS.md                 # 8 项关键决策记录
│   └── ROADMAP.md                   # Phase 0-4 路线图
├── scripts/
│   ├── init.sh                      # WP-CLI 幂等站点初始化（含密码/salt 校验、失败终止）
│   ├── demo-data.php                # 可收敛幂等商品数据导入（含错误检查、运行后验证）
│   ├── block-name-scanner.sh        # WC 11 区块名称扫描器（已运行通过）
│   └── smoke-check.sh               # 初始化后 Smoke Check（需 Docker 运行）
└── wp-content/
    ├── plugins/commerce-core/       # 自研核心插件
    │   ├── commerce-core.php        # 插件入口
    │   ├── composer.json             # PHPCS/WPCS + PHPStan + PHPUnit
    │   ├── src/
    │   │   ├── Autoload.php          # PSR-4 自动加载
    │   │   ├── Plugin.php            # 生命周期管理
    │   │   ├── Module/               # 模块系统
    │   │   ├── Config/               # 值对象配置
    │   │   ├── Adapter/              # 适配器接口
    │   │   ├── Admin/                # 管理页面
    │   │   ├── Rest/                 # REST API
    │   │   ├── Cli/                  # WP-CLI 命令
    │   │   └── Util/
    │   │       ├── Idempotency.php    # 幂等操作助手
    │   │       └── Logger.php         # 结构化日志（可注入 handler、自动脱敏）
    │   ├── tests/phpunit/            # 单元测试（22 个，已编写未执行）
    │   └── ...
    └── themes/commerce-block-theme/  # Gutenberg FSE 主题
        ├── style.css                  # 主题头（WCAG 2.2 AA target, not yet audited）
        ├── theme.json                 # 设计令牌（v3 schema）
        ├── styles/                    # 3 套 Style Variations
        ├── templates/                 # 12 个模板（WC 11 区块名称已修正）
        ├── parts/                     # 3 个模板部件
        ├── patterns/                  # 10 个区块样板
        ├── package.json               # Node 依赖
        ├── package-lock.json          # 锁定依赖版本（102KB，189 包）
        └── ...
```

---

## 6. 关键架构决定

| ID | 决策 | 理由 |
|---|---|---|
| D-001 | 单一母版多站部署（非 Multisite） | 每站独立 DB/SSL/CDN，配置替换即可换站 |
| D-002 | Gutenberg FSE Block Theme | theme.json 令牌系统支持 JSON 换肤 |
| D-003 | 插件承载业务逻辑 | 插件跨主题切换可用，逻辑可移植 |
| D-004 | 适配器模式集成第三方 | 厂商无关，接口先行 |
| D-005 | 模块注册系统 | 避免 mega-file，模块自包含 |
| D-006 | Docker Compose 开发环境 | 跨平台一致，无需本地 PHP/MySQL |
| D-007 | 不修改 WP/WC 核心 | 清洁升级，hook + Block 扩展 |
| D-008 | WCAG 2.2 AA 无障碍目标 | 欧盟 EAA 合规要求（尚未审计） |

---

## 7. 未验证项、风险与下一阶段建议

### 未验证项

1. **Docker 环境无法运行** — 本机无 Docker/PHP/Composer，所有依赖 Docker 的验证均未执行。但所有工程文件已完成并可审查。
2. **PHPUnit 测试无法运行** — 测试代码已编写（22 个测试覆盖 Logger 脱敏 12 个 + Idempotency 10 个），但 PHP 未安装。测试 bootstrap 包含 WP/WC 函数 stubs，无需 WordPress 即可运行。**已配置但未执行。**
3. **PHPCS / PHPStan 无法运行** — 配置文件（phpcs.xml.dist、phpstan.neon）已就绪，但 Composer 未安装，依赖未安装。**已配置但未执行。**
4. **composer.lock 无法生成** — 本机无 PHP/Composer。CI 环境 `composer install` 将自动生成。建议首次 CI 运行后提交。
5. **WooCommerce 模板兼容性** — 主题模板使用了 WC 11 正确区块名称（经 block-name-scanner 验证），但未在实际 WC 11.0 环境中验证渲染。
6. **演示商品导入** — `demo-data.php` 逻辑完整（可收敛幂等、GD 生成纯色占位图、错误检查、运行后验证），但未在实际 WP-CLI 中执行。
7. **Smoke Check** — `scripts/smoke-check.sh` 已编写，但需 Docker 环境运行。

### 风险

| 风险 | 严重程度 | 缓解方案 |
|---|---|---|
| WooCommerce 11.0 Blocks 兼容性 | 中 | 区块名称已通过扫描器验证，需在 Docker 环境中实测渲染 |
| composer.lock 缺失 | 中 | CI 首次运行 `composer install` 生成后提交 |
| theme.json v3 schema | 低 | WP 7.0.2 使用 v3 schema，已正确配置 |
| PHP 8.3 兼容性 | 低 | Docker 镜像使用 PHP 8.3，满足 WP/WC 要求 |
| 前端占位图质量 | 低 | 纯色 GD 图片仅用于开发，生产需替换真实品牌图 |
| WCAG 2.2 AA 未审计 | 中 | 已明确标记为"target, not yet audited"，需正式审计后才能声称合规 |

### 下一步命令（用户安装依赖后执行）

```bash
# 1. 安装 Docker (macOS)
brew install --cask docker
open -a Docker

# 2. 安装 PHP + Composer (可选，用于本地 lint/test)
brew install php composer

# 3. 复制环境配置
cp .env.example .env
# 编辑 .env — 设置强密码（≥12 字符）并生成 WordPress salts
# Salts: https://api.wordpress.org/secret-key/1.1/salt/

# 4. 启动开发环境
docker compose up -d

# 5. 初始化 WordPress + WooCommerce 11.0 + 主题 + 演示数据
docker compose --profile cli run --rm wpcli bash /scripts/init.sh

# 6. 运行 Smoke Check
docker compose --profile cli run --rm wpcli bash /scripts/smoke-check.sh

# 7. 验证幂等性（再运行一次 init）
docker compose --profile cli run --rm wpcli bash /scripts/init.sh

# 8. 访问网站
open http://localhost:8080

# 9. 运行 PHP 质量检查（需安装 PHP + Composer 后）
cd wp-content/plugins/commerce-core
composer install        # 首次运行生成 composer.lock — 提交它
composer check-all      # lint + phpcs + phpstan + test

# 10. 运行前端质量检查
cd wp-content/themes/commerce-block-theme
npm ci
npm run lint

# 11. 运行区块名称扫描
bash scripts/block-name-scanner.sh
```

---

## 8. Git 提交历史

```
3fad054 feat: initialize Phase 0 project structure — Docker Compose, tooling configs, docs, init script
ffc8ac4 feat: commerce-core plugin skeleton — modules, config, adapters, REST, tests
99272d4 feat: commerce-block-theme — FSE block theme with templates, patterns, style variations
28e58ee feat: idempotent demo data initialization script — 10 products, attributes, categories
6866559 fix: ESLint auto-fix var→const + Stylelint config compatibility for ESLint 9 flat config
cf1c882 docs: Phase 0 verification report — 86 files, 6623 lines, all checks documented
[本次提交] fix: Phase 0 code review — WC block names, Docker env, tests, demo data, deps, automated checks
```

---

## 9. 对原文档要求的逐项回应

| 原文要求 | 实现情况 |
|---|---|
| 可重复启动的本地开发环境 | ✅ docker-compose.yml（WordPress 7.0.2 + PHP 8.3 + MariaDB 11.8 LTS + phpMyAdmin + WP-CLI 2.12.0 + Node 22） |
| commerce-core 插件 | ✅ 模块化注册机制，非单一入口文件 |
| commerce-block-theme 主题 | ✅ 合法可激活 FSE block theme（WC 11 区块名称已修正） |
| theme.json 设计令牌 | ✅ v3 schema，颜色/字体/间距/圆角/阴影/容器宽度/断点 |
| Style Variations | ✅ default/light/dark 三套 |
| header/footer/checkout-header 模板部件 | ✅ |
| 全部必需模板 | ✅ 12 个（含 page-wide/page-blank） |
| Cart/Checkout 遵循 WC Blocks | ✅ 使用 `<!-- wp:woocommerce/cart /-->` / `<!-- wp:woocommerce/checkout /-->` 区块（非短代码） |
| 首页区块样板 | ✅ 10 个（announcement-bar → footer-info） |
| 现代欧美时尚电商 UI | ✅ 中性黑白灰、强网格、移动优先、图片优先 |
| 商品变体 color/size | ✅ 含鞋码 shoe_size 属性 |
| 可重复执行的 WP-CLI 初始化 | ✅ init.sh + demo-data.php 均可收敛幂等 |
| 纯色占位图 | ✅ GD 生成，无第三方素材（GD 不可用时明确警告） |
| WP Coding Standards | ✅ phpcs.xml.dist 配置 WPCS（**已配置，未执行**） |
| escaping/sanitization/nonce/capability | ✅ 全部实施 |
| WCAG 2.2 AA | ✅ skip-link、focus-visible、prefers-reduced-motion、颜色对比（**target, not yet audited**） |
| 自研插件关键逻辑测试 | ✅ 22 个单元测试已编写（**未执行**——无 PHP 环境） |
| Composer validate | ✅ 结构人工审查合法（**`composer validate` 未执行**——无 Composer） |
| 依赖可重复 | ✅ package-lock.json 已生成；npm ci 确定性安装；composer.lock 待 CI 生成 |
| 区块名称扫描 | ✅ block-name-scanner.sh 已运行通过（25 文件，0 废弃） |
| Smoke Check | ✅ smoke-check.sh 已编写（**未执行**——需 Docker） |
| git diff 自查 | ✅ 无密钥/DB/上传/垃圾 |
| 不伪造成功 | ✅ 精确列出未运行项和原因，PHPCS/PHPStan/PHPUnit 标记为"已配置但未执行" |

---

*Phase 0 工程母版第二轮修复已交付。安装 Docker 后即可启动完整验证。本报告不声称任何未实际执行的检查已通过。*
