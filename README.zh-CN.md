# 低端影视 PbootCMS 插件

中文 | [English](README.md)

[低端影视](https://ddys.io/) API 的官方 PbootCMS 插件，用于在 PbootCMS 站点中通过模板标签、独立页面、本地代理和求片表单展示低端影视内容。

- GitHub 仓库：[ddysiodev/ddys-pbootcms-plugin](https://github.com/ddysiodev/ddys-pbootcms-plugin)
- 插件标识：`ddys_open`
- 推荐环境：PbootCMS 3.x，PHP 5.6+ 或更新版本，UTF-8 站点
- 主要目录：`apps/ddys_open/`、`apps/home/controller/DdysController.php`、`apps/admin/controller/DdysOpenController.php`、`static/ddys_open/`、`data/ddys_open/`

## 功能

- PbootCMS 模板标签：`{ddys:latest limit=12}`、`{ddys:movie slug=...}` 等。
- 兼容短代码：`[ddys_latest limit="12"]` 等。
- 独立前台页面：最新、热门、搜索、日历、影片详情、片单、求片。
- 本地 JSON 代理：通过站点服务端请求低端影视 API。
- 服务端求片提交：API Key 只保存在服务端，支持 nonce 校验和 IP 限流。
- 后台配置：API 地址、API Key、缓存、样式、来源链接、伪静态和求片表单。
- 后台诊断：连接测试、缓存统计、缓存清理、标签生成器。
- 文件缓存：缓存接口结果并清理过期文件。
- 伪静态规则：提供 Apache、Nginx、IIS 示例。
- 图标资源：来自主站图标集，已包含常用尺寸。

## 安装

1. 下载 Release 中的 `ddys-pbootcms-plugin-v0.1.0.zip`。
2. 解压后把 `apps/`、`static/`、`data/` 上传到 PbootCMS 站点根目录。
3. 打开后台地址 `/admin.php/DdysOpen/index` 或 `/index.php/admin/DdysOpen/index`，保存 API 地址、缓存和展示配置。
4. 执行“测试低端影视 API”，确认站点服务器可以访问接口。
5. 按下面的 ExtLabelController 说明合并标签扩展代码。

如果 `data/ddys_open` 或 `data/ddys_open/cache` 提示不可写，请给这两个目录设置 Web 进程可写权限。

## 标签接入

PbootCMS 的自定义标签入口是 `apps/home/controller/ExtLabelController.php`。安装包不会覆盖这个文件，避免破坏站长已有扩展。

如果站点没有该文件，可以参考：

```text
apps/ddys_open/install/ExtLabelController.example.php
```

如果站点已有 `ExtLabelController.php`，在 `run($content)` 中加入：

```php
require_once dirname(dirname(__DIR__)) . '/ddys_open/bootstrap.php';
\ddys_open_bootstrap();
$content = \ddys_open_parse_labels($content);
```

推荐写法：

```php
public function run($content)
{
    require_once dirname(dirname(__DIR__)) . '/ddys_open/bootstrap.php';
    \ddys_open_bootstrap();
    $content = \ddys_open_parse_labels($content);
    return $content;
}
```

## PbootCMS 标签

```text
{ddys:movies type=movie per_page=24}
{ddys:latest type=movie limit=12}
{ddys:hot limit=10}
{ddys:search}
{ddys:suggest q=interstellar}
{ddys:calendar year=2026 month=7}
{ddys:movie slug=this-tempting-madness}
{ddys:sources slug=this-tempting-madness}
{ddys:related slug=this-tempting-madness}
{ddys:comments slug=this-tempting-madness per_page=20}
{ddys:collections per_page=10}
{ddys:collection slug=best-sci-fi per_page=12}
{ddys:shares per_page=10}
{ddys:share id=1}
{ddys:requests per_page=10}
{ddys:activities per_page=10}
{ddys:user username=demo}
{ddys:types}
{ddys:genres}
{ddys:regions}
{ddys:requestform}
```

## 短代码

```text
[ddys_movies type="movie" per_page="24"]
[ddys_latest type="movie" limit="12"]
[ddys_hot limit="10"]
[ddys_search]
[ddys_suggest q="interstellar"]
[ddys_calendar year="2026" month="7"]
[ddys_movie slug="this-tempting-madness"]
[ddys_sources slug="this-tempting-madness"]
[ddys_related slug="this-tempting-madness"]
[ddys_comments slug="this-tempting-madness" per_page="20"]
[ddys_collections per_page="10"]
[ddys_collection slug="best-sci-fi" per_page="12"]
[ddys_shares per_page="10"]
[ddys_share id="1"]
[ddys_requests per_page="10"]
[ddys_activities per_page="10"]
[ddys_user username="demo"]
[ddys_types]
[ddys_genres]
[ddys_regions]
[ddys_request_form]
```

## 独立页面

动态地址会随 PbootCMS 地址模式变化。常见普通模式示例：

```text
/index.php/ddys/index
/index.php/ddys/index?view=hot
/index.php/ddys/index?view=search
/index.php/ddys/index?view=calendar
/index.php/ddys/index?view=movie&slug=this-tempting-madness
/index.php/ddys/index?view=collections
/index.php/ddys/index?view=requests
/index.php/ddys/api?route=latest&limit=12
/index.php/ddys/request
```

后台页面：

```text
/admin.php/DdysOpen/index
/index.php/admin/DdysOpen/index
```

## 伪静态

在插件后台启用“伪静态链接”后，默认基础路径是 `ddys`：

```text
/ddys/
/ddys/hot
/ddys/search
/ddys/calendar
/ddys/movie/this-tempting-madness
/ddys/collections
/ddys/requests
/ddys/api
/ddys/request-submit
```

如果把基础路径改成其他值，下面规则中的 `ddys` 也要同步替换。

### Apache

```apache
RewriteEngine On
RewriteRule ^ddys/?$ index.php/ddys/index [L,QSA]
RewriteRule ^ddys/(hot|search|calendar|collections|requests)/?$ index.php/ddys/index?view=$1 [L,QSA]
RewriteRule ^ddys/movie/([^/]+)/?$ index.php/ddys/index?view=movie&slug=$1 [L,QSA]
RewriteRule ^ddys/api/?$ index.php/ddys/api [L,QSA]
RewriteRule ^ddys/request-submit/?$ index.php/ddys/request [L,QSA]
```

### Nginx

```nginx
rewrite ^/ddys/?$ /index.php/ddys/index last;
rewrite ^/ddys/(hot|search|calendar|collections|requests)/?$ /index.php/ddys/index?view=$1 last;
rewrite ^/ddys/movie/([^/]+)/?$ /index.php/ddys/index?view=movie&slug=$1 last;
rewrite ^/ddys/api/?$ /index.php/ddys/api last;
rewrite ^/ddys/request-submit/?$ /index.php/ddys/request last;
```

### IIS

```xml
<rule name="DDYS PbootCMS Latest" stopProcessing="true">
  <match url="^ddys/?$" />
  <action type="Rewrite" url="index.php/ddys/index" appendQueryString="true" />
</rule>
<rule name="DDYS PbootCMS Views" stopProcessing="true">
  <match url="^ddys/(hot|search|calendar|collections|requests)/?$" />
  <action type="Rewrite" url="index.php/ddys/index?view={R:1}" appendQueryString="true" />
</rule>
<rule name="DDYS PbootCMS Movie" stopProcessing="true">
  <match url="^ddys/movie/([^/]+)/?$" />
  <action type="Rewrite" url="index.php/ddys/index?view=movie&amp;slug={R:1}" appendQueryString="true" />
</rule>
<rule name="DDYS PbootCMS API" stopProcessing="true">
  <match url="^ddys/api/?$" />
  <action type="Rewrite" url="index.php/ddys/api" appendQueryString="true" />
</rule>
<rule name="DDYS PbootCMS Request Submit" stopProcessing="true">
  <match url="^ddys/request-submit/?$" />
  <action type="Rewrite" url="index.php/ddys/request" appendQueryString="true" />
</rule>
```

## 本地检查

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

检查覆盖目录结构、控制器、标签覆盖、短代码覆盖、ExtLabel 示例、伪静态文档、图标尺寸、运行时文件排除和敏感文本。

