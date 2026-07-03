# DDYS PbootCMS Plugin

[中文](README.zh-CN.md) | English

Official PbootCMS plugin for the [DDYS](https://ddys.io/) API. It adds DDYS template tags, standalone frontend pages, a local JSON proxy, caching, diagnostics, and a server-side request form to PbootCMS sites.

- GitHub repository: [ddysiodev/ddys-pbootcms-plugin](https://github.com/ddysiodev/ddys-pbootcms-plugin)
- Plugin ID: `ddys_open`
- Recommended environment: PbootCMS 3.x, PHP 5.6+ or newer, UTF-8 site
- Main paths: `apps/ddys_open/`, `apps/home/controller/DdysController.php`, `apps/admin/controller/DdysOpenController.php`, `static/ddys_open/`, `data/ddys_open/`

## Features

- PbootCMS template tags such as `{ddys:latest limit=12}` and `{ddys:movie slug=...}`.
- Compatible shortcode parser such as `[ddys_latest limit="12"]`.
- Standalone pages for latest, hot, search, calendar, movie detail, collections, and requests.
- Local JSON proxy through the site server.
- Server-side request submission with nonce checks and IP rate limiting.
- Admin settings for API URL, API Key, cache TTLs, layout, source links, pretty URLs, and request form.
- Admin diagnostics for connection testing, cache count, cache flush, and tag generation.
- File cache with expired-file pruning.
- Apache, Nginx, and IIS rewrite examples.
- Icon assets copied from the DDYS site icon set.

## Installation

1. Download `ddys-pbootcms-plugin-v0.1.0.zip` from Releases.
2. Upload `apps/`, `static/`, and `data/` to the PbootCMS site root.
3. Open `/admin.php/DdysOpen/index` or `/index.php/admin/DdysOpen/index`, then save API, cache, display, and request-form settings.
4. Run “Test DDYS API” in the plugin admin page.
5. Merge the ExtLabelController integration described below.

If `data/ddys_open` or `data/ddys_open/cache` is not writable, grant write permission to the web server user for those two directories.

## Label Integration

PbootCMS calls `apps/home/controller/ExtLabelController.php` for custom template tags. This package does not overwrite that file because many sites already customize it.

If your site does not have the file, use:

```text
apps/ddys_open/install/ExtLabelController.example.php
```

If it already exists, add this inside `run($content)`:

```php
require_once dirname(dirname(__DIR__)) . '/ddys_open/bootstrap.php';
\ddys_open_bootstrap();
$content = \ddys_open_parse_labels($content);
```

## PbootCMS Tags

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

## Shortcodes

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

## Standalone Pages

Dynamic URLs depend on the PbootCMS URL mode. Common normal-mode examples:

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

Admin URLs:

```text
/admin.php/DdysOpen/index
/index.php/admin/DdysOpen/index
```

## Pretty URLs

After enabling pretty URLs in the plugin admin page, the default base path is `ddys`:

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

Replace `ddys` in the rules below if you change the base path.

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

## Local Checks

```bash
node tools/check.mjs
node --test tests/*.test.mjs
```

Checks cover directory layout, controllers, tag coverage, shortcode coverage, ExtLabel example, rewrite docs, icon dimensions, ignored runtime files, and sensitive wording.

