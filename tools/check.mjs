import { promises as fs } from 'node:fs';
import path from 'node:path';
import process from 'node:process';

const root = process.cwd();
const requiredFiles = [
  'README.md',
  'README.zh-CN.md',
  'LICENSE',
  '.gitignore',
  'apps/ddys_open/bootstrap.php',
  'apps/ddys_open/security.php',
  'apps/ddys_open/storage.php',
  'apps/ddys_open/client.php',
  'apps/ddys_open/render.php',
  'apps/ddys_open/labels.php',
  'apps/ddys_open/templates/page.php',
  'apps/ddys_open/install/ExtLabelController.example.php',
  'apps/home/controller/DdysController.php',
  'apps/admin/controller/DdysOpenController.php',
  'static/ddys_open/css/frontend.css',
  'static/ddys_open/css/admin.css',
  'static/ddys_open/js/frontend.js',
  'static/ddys_open/js/admin.js',
  'static/ddys_open/images/icon-16.png',
  'static/ddys_open/images/icon-32.png',
  'static/ddys_open/images/icon-192.png',
  'static/ddys_open/images/icon-512.png',
  'static/ddys_open/images/logo.png',
  'data/ddys_open/.htaccess',
  'data/ddys_open/cache/.htaccess'
];

const shortcodes = [
  'ddys_movies',
  'ddys_latest',
  'ddys_hot',
  'ddys_search',
  'ddys_suggest',
  'ddys_calendar',
  'ddys_movie',
  'ddys_sources',
  'ddys_related',
  'ddys_comments',
  'ddys_collections',
  'ddys_collection',
  'ddys_shares',
  'ddys_share',
  'ddys_requests',
  'ddys_activities',
  'ddys_user',
  'ddys_types',
  'ddys_genres',
  'ddys_regions',
  'ddys_request_form'
];

const labels = [
  'movies',
  'latest',
  'hot',
  'search',
  'suggest',
  'calendar',
  'movie',
  'sources',
  'related',
  'comments',
  'collections',
  'collection',
  'shares',
  'share',
  'requests',
  'activities',
  'user',
  'types',
  'genres',
  'regions',
  'requestform'
];

const failures = [];

async function read(rel) {
  return fs.readFile(path.join(root, rel), 'utf8');
}

async function exists(rel) {
  try {
    await fs.access(path.join(root, rel));
    return true;
  } catch {
    return false;
  }
}

function assert(condition, message) {
  if (!condition) failures.push(message);
}

async function walk(dir = root) {
  const entries = await fs.readdir(dir, { withFileTypes: true });
  const files = [];
  for (const entry of entries) {
    const full = path.join(dir, entry.name);
    if (entry.isDirectory()) {
      files.push(...await walk(full));
    } else {
      files.push(full);
    }
  }
  return files;
}

function pngDimensions(buffer) {
  if (buffer.toString('ascii', 1, 4) !== 'PNG') {
    return null;
  }
  return {
    width: buffer.readUInt32BE(16),
    height: buffer.readUInt32BE(20)
  };
}

function phpShape(source, rel) {
  assert(!source.startsWith('\uFEFF'), `${rel} must not contain BOM`);
  assert(!/\?>\s*$/.test(source), `${rel} should omit closing PHP tag`);
  assert(!/dirname\s*\([^,\n]+,\s*\d+\s*\)/.test(source), `${rel} uses dirname levels not compatible with old PHP`);
  assert(!/\?\?/.test(source), `${rel} uses null coalescing not compatible with old PHP`);
  if (!/apps\/(home|admin)\/controller|ExtLabelController\.example\.php/.test(rel)) {
    assert(!/\bnamespace\b/.test(source), `${rel} should not define a namespace`);
  }
  let braces = 0;
  let parens = 0;
  let brackets = 0;
  let quote = '';
  let escaped = false;
  for (const ch of source) {
    if (quote) {
      if (escaped) {
        escaped = false;
      } else if (ch === '\\') {
        escaped = true;
      } else if (ch === quote) {
        quote = '';
      }
      continue;
    }
    if (ch === '"' || ch === "'") {
      quote = ch;
      continue;
    }
    if (ch === '{') braces++;
    if (ch === '}') braces--;
    if (ch === '(') parens++;
    if (ch === ')') parens--;
    if (ch === '[') brackets++;
    if (ch === ']') brackets--;
    if (braces < 0 || parens < 0 || brackets < 0) {
      failures.push(`${rel} has an early closing delimiter`);
      return;
    }
  }
  assert(braces === 0, `${rel} has unbalanced braces`);
  assert(parens === 0, `${rel} has unbalanced parentheses`);
  assert(brackets === 0, `${rel} has unbalanced brackets`);
  assert(!quote, `${rel} has an unterminated string`);
}

for (const file of requiredFiles) {
  assert(await exists(file), `Missing required file: ${file}`);
}

const labelsPhp = await read('apps/ddys_open/labels.php');
const renderPhp = await read('apps/ddys_open/render.php');
const clientPhp = await read('apps/ddys_open/client.php');
const securityPhp = await read('apps/ddys_open/security.php');
const homeController = await read('apps/home/controller/DdysController.php');
const adminController = await read('apps/admin/controller/DdysOpenController.php');
const extExample = await read('apps/ddys_open/install/ExtLabelController.example.php');
const readmeZh = await read('README.zh-CN.md');
const readmeEn = await read('README.md');
const frontendJs = await read('static/ddys_open/js/frontend.js');
const adminJs = await read('static/ddys_open/js/admin.js');
const gitignore = await read('.gitignore');

for (const shortcode of shortcodes) {
  assert(labelsPhp.includes(`'${shortcode}'`) || labelsPhp.includes(`"${shortcode}"`), `labels.php missing ${shortcode}`);
  assert(readmeZh.includes(`[${shortcode}`), `Chinese README missing ${shortcode}`);
}
for (const label of labels) {
  assert(readmeZh.includes(`{ddys:${label}`), `Chinese README missing {ddys:${label}}`);
}

assert(labelsPhp.includes('ddys_open_parse_labels'), 'PbootCMS label parser is missing');
assert(labelsPhp.includes('requestform'), 'requestform label alias is missing');
assert(renderPhp.includes('ddys_open_render_full_page'), 'standalone full page renderer is missing');
assert(renderPhp.includes("isset($dayData['shows'])"), 'calendar renderer must handle days.*.shows payloads');
assert(renderPhp.includes("'cn_name'"), 'card renderer must handle calendar cn_name titles');
assert(renderPhp.includes("'episode'"), 'card renderer must show calendar episode metadata');
assert(renderPhp.includes("'related'") && renderPhp.includes("'series'"), 'list renderer must handle related API payload groups');
assert(clientPhp.includes('ddys_open_proxy_response') && clientPhp.includes('ddys_open_handle_request_form'), 'proxy/request handlers are missing');
assert(securityPhp.includes('core\\basic\\Url') && securityPhp.includes('ddys_open_pboot_url'), 'PbootCMS URL fallback is missing');
assert(/function ddys_open_get[\s\S]*isset\(\$_GET\[\$key\]\)[\s\S]*function_exists\('get'\)/.test(securityPhp), 'GET reader must prefer raw superglobal before Pboot filtering');
assert(/function ddys_open_post[\s\S]*isset\(\$_POST\[\$key\]\)[\s\S]*function_exists\('post'\)/.test(securityPhp), 'POST reader must prefer raw superglobal before Pboot filtering');
assert(homeController.includes('class DdysController') && homeController.includes('public function api()') && homeController.includes('public function request()'), 'frontend controller is incomplete');
assert(adminController.includes('class DdysOpenController') && adminController.includes('ddys_open_admin_logged_in') && adminController.includes('ddys-pbootcms-label-output'), 'admin controller is incomplete');
assert(extExample.includes('ExtLabelController') && extExample.includes('ddys_open_parse_labels'), 'ExtLabel example is incomplete');
assert(!/apps\/home\/controller\/ExtLabelController\.php/.test(requiredFiles.join('\n')), 'package must not require overwriting ExtLabelController.php');
assert(frontendJs.includes('!window.fetch') && frontendJs.includes('FormData'), 'frontend request JS must gracefully fall back without fetch/FormData');
assert(adminJs.includes('ddys-pbootcms-label-output') && adminJs.includes('{ddys:'), 'admin tag generator is incomplete');
assert(gitignore.includes('data/ddys_open/config.php') && gitignore.includes('data/ddys_open/cache/*.php'), 'runtime files are not ignored');
assert(readmeZh.includes('不能直接覆盖') || readmeZh.includes('不会覆盖'), 'Chinese README must warn about ExtLabelController overwrite');
assert(readmeZh.includes('/ddys/request-submit') && readmeZh.includes('/ddys/api'), 'Chinese README missing pretty URL endpoints');
assert(readmeEn.includes('/ddys/request-submit') && readmeEn.includes('/ddys/api'), 'English README missing pretty URL endpoints');
assert(!/Open API/i.test(readmeZh + readmeEn), 'README should use DDYS API or 低端影视 API wording, not Open API');
assert(!/Composer|third-party CDN|第三方 CDN/.test(readmeZh + readmeEn), 'README contains unnecessary dependency wording');

for (const [size, rel] of [
  [16, 'static/ddys_open/images/icon-16.png'],
  [32, 'static/ddys_open/images/icon-32.png'],
  [192, 'static/ddys_open/images/icon-192.png'],
  [512, 'static/ddys_open/images/icon-512.png']
]) {
  const buffer = await fs.readFile(path.join(root, rel));
  const dim = pngDimensions(buffer);
  assert(dim && dim.width === size && dim.height === size, `${rel} must be ${size}x${size}`);
}

const allFiles = await walk();
for (const full of allFiles) {
  const rel = path.relative(root, full).replace(/\\/g, '/');
  assert(!/(^|\/)(\.env|node_modules|vendor)(\/|$)/i.test(rel), `Forbidden file in repository: ${rel}`);
  assert(!/\.(zip|log|bak)$/i.test(rel), `Forbidden file in repository: ${rel}`);
  assert(!/data\/ddys_open\/config\.php$/i.test(rel), `Runtime config must not be committed: ${rel}`);
  if (/\.php$/i.test(rel)) {
    phpShape(await read(rel), rel);
  }
}

if (failures.length > 0) {
  console.error(failures.map((failure) => `- ${failure}`).join('\n'));
  process.exit(1);
}

console.log(`PbootCMS plugin check passed (${allFiles.length} files).`);
