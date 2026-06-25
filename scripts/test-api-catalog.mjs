/**
 * Test all api_catalog endpoints against BASE_URL.
 * Usage: node scripts/test-api-catalog.mjs [baseUrl]
 */
import { execSync } from 'child_process';
import path from 'path';

const base = (process.argv[2] || 'https://superfinelabels.in/cp').replace(/\/$/, '');
const root = process.cwd();

function loadCatalog() {
  const phpScript = path.join(root, 'scripts/export-api-catalog.php');
  const json = execSync(`php "${phpScript}"`, { encoding: 'utf8' });
  return JSON.parse(json);
}

function buildUrl(pathStr, query) {
  const url = new URL(`${base}/${pathStr.replace(/^\//, '')}`);
  Object.entries(query || {}).forEach(([k, v]) => {
    if (k && v !== undefined && v !== null && String(v) !== '') {
      url.searchParams.set(k, String(v));
    }
  });
  return url.toString();
}

async function testOne(ep) {
  const url = buildUrl(ep.path, ep.query);
  const opts = {
    method: ep.method || 'GET',
    headers: { Accept: 'application/json' },
  };

  if (['POST', 'PUT', 'PATCH'].includes(ep.method) && ep.body && Object.keys(ep.body).length) {
    if (ep.body_type === 'json') {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(ep.body);
    } else {
      opts.headers['Content-Type'] = 'application/x-www-form-urlencoded';
      opts.body = new URLSearchParams(
        Object.fromEntries(Object.entries(ep.body).map(([k, v]) => [k, String(v)]))
      ).toString();
    }
  }

  const t0 = Date.now();
  try {
    const res = await fetch(url, opts);
    const text = await res.text();
    let json = null;
    try {
      json = JSON.parse(text);
    } catch {
      /* html or plain */
    }
    const ms = Date.now() - t0;
    const authExpected = ep.auth && (res.status === 401 || res.status === 403);
    const validationWithoutAuth =
      ep.auth &&
      ['POST', 'PUT', 'PATCH'].includes(ep.method) &&
      json &&
      json.success === false &&
      res.status < 400;
    const allowSampleNotFound = ep.id === 'blog_detail';
    const ok =
      (res.status !== 404 || allowSampleNotFound) &&
      !text.includes('<!DOCTYPE html>') &&
      res.status < 500 &&
      (json === null ||
        json.success !== false ||
        authExpected ||
        validationWithoutAuth);
    const issue =
      res.status === 404
        ? allowSampleNotFound ? null : '404'
        : text.includes('<!DOCTYPE')
          ? 'HTML not JSON'
          : json && json.success === false && !authExpected && !validationWithoutAuth && res.status < 400
            ? `fail: ${json.message || 'unknown'}`
            : res.status >= 500
              ? `HTTP ${res.status}`
              : null;
    return { ...ep, url, status: res.status, ms, ok: !issue, issue };
  } catch (e) {
    return { ...ep, url, status: 0, ok: false, issue: e.message };
  }
}

const catalog = loadCatalog();
const endpoints = [];
for (const group of catalog.groups || []) {
  for (const ep of group.endpoints || []) {
    endpoints.push(ep);
  }
}

console.log(`Testing ${endpoints.length} endpoints at ${base}\n`);
const results = [];
for (const ep of endpoints) {
  const r = await testOne(ep);
  results.push(r);
  const mark = r.ok ? 'OK' : 'FAIL';
  console.log(`${mark} [${r.status}] ${r.method} ${r.path} — ${r.name}${r.issue ? ' — ' + r.issue : ''}`);
}

const failed = results.filter((r) => !r.ok);
console.log(`\n${results.length - failed.length}/${results.length} passed, ${failed.length} failed`);
if (failed.length) {
  console.log('\nFailed:');
  failed.forEach((f) => console.log(`  ${f.method} ${f.path} (${f.id}): ${f.issue}`));
  process.exit(1);
}
