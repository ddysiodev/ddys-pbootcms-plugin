import test from 'node:test';
import assert from 'node:assert/strict';
import { promises as fs } from 'node:fs';
import path from 'node:path';

const root = process.cwd();

async function read(rel) {
  return fs.readFile(path.join(root, rel), 'utf8');
}

test('PbootCMS package exposes expected install paths', async () => {
  for (const rel of [
    'apps/ddys_open/bootstrap.php',
    'apps/ddys_open/labels.php',
    'apps/home/controller/DdysController.php',
    'apps/admin/controller/DdysOpenController.php',
    'apps/ddys_open/install/ExtLabelController.example.php',
    'static/ddys_open/css/frontend.css',
    'data/ddys_open/.htaccess'
  ]) {
    await assert.doesNotReject(fs.access(path.join(root, rel)));
  }
});

test('label and shortcode parser covers the full API surface', async () => {
  const labels = await read('apps/ddys_open/labels.php');
  for (const name of [
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
  ]) {
    assert.match(labels, new RegExp(`['"]${name}['"]`));
  }
  assert.match(labels, /ddys_open_parse_labels/);
  assert.match(labels, /requestform/);
});

test('controllers expose frontend, proxy, request, check, and admin pages', async () => {
  const home = await read('apps/home/controller/DdysController.php');
  const admin = await read('apps/admin/controller/DdysOpenController.php');
  assert.match(home, /class DdysController/);
  assert.match(home, /public function index/);
  assert.match(home, /public function api/);
  assert.match(home, /public function request/);
  assert.match(home, /public function check/);
  assert.match(admin, /class DdysOpenController/);
  assert.match(admin, /ddys_open_admin_logged_in/);
  assert.match(admin, /ddys-pbootcms-label-output/);
});

test('ExtLabel integration is documented without overwriting the site file', async () => {
  const example = await read('apps/ddys_open/install/ExtLabelController.example.php');
  const zh = await read('README.zh-CN.md');
  assert.match(example, /ddys_open_parse_labels/);
  assert.match(zh, /不会覆盖/);
  assert.match(zh, /ExtLabelController\.example\.php/);
});

test('request form stays server-side and guarded', async () => {
  const client = await read('apps/ddys_open/client.php');
  const render = await read('apps/ddys_open/render.php');
  const security = await read('apps/ddys_open/security.php');
  const frontend = await read('static/ddys_open/js/frontend.js');
  assert.match(client, /ddys_open_handle_request_form/);
  assert.match(client, /api_key/);
  assert.match(client, /ddys_open_check_rate_limit/);
  assert.match(render, /data-ddys-pbootcms-request-form/);
  assert.match(security, /ddys_open_check_nonce/);
  assert.match(frontend, /!window\.fetch/);
});

test('runtime files are ignored', async () => {
  const gitignore = await read('.gitignore');
  assert.match(gitignore, /data\/ddys_open\/config\.php/);
  assert.match(gitignore, /data\/ddys_open\/cache\/\*\.php/);
});

