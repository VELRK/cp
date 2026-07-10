#!/usr/bin/env node
/**
 * Build static Next.js export + package PHP app for upload to PHP/MySQL hosting.
 *
 * Usage:
 *   node scripts/build-deploy.mjs
 *   BACKEND_URL=https://your-site.com/cp node scripts/build-deploy.mjs --git
 *
 * Output: deploy/release/ (local preview)
 * With --git: copies out/ + production .htaccess to repo root (legacy — CI build preferred)
 */

import { execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(__dirname, '..');
const releaseDir = path.join(root, 'deploy', 'release');
const stashDir = path.join(root, '.deploy-stash');
const apiDir = path.join(root, 'app', 'api');

const backendUrl = (process.env.BACKEND_URL || 'http://127.0.0.1:8080/cp').replace(/\/$/, '');

function log(msg) {
  console.log(`[build-deploy] ${msg}`);
}

function run(cmd, env = {}) {
  execSync(cmd, {
    cwd: root,
    stdio: 'inherit',
    env: { ...process.env, ...env },
  });
}

async function fetchJson(url) {
  try {
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) return null;
    return await res.json();
  } catch {
    return null;
  }
}

async function loadBuildParams() {
  const search = await fetchJson(`${backendUrl}/index.php/api/nb/search?limit=500`);
  const slugs = Array.isArray(search?.items)
    ? search.items.map((i) => i.slug).filter(Boolean)
    : [];
  if (slugs.length === 0) {
    log('Warning: no property slugs from API — static export will use a placeholder page only');
    log(`Tried: ${backendUrl}/index.php/api/nb/search?limit=500`);
  } else {
    log(`Fetched ${slugs.length} property slugs from API`);
  }

  const blogs = await fetchJson(`${backendUrl}/index.php/api/blogs`);
  const blogIds = Array.isArray(blogs?.blogs)
    ? blogs.blogs.map((b) => String(b.id)).filter(Boolean)
    : Array.isArray(blogs?.data)
      ? blogs.data.map((b) => String(b.id)).filter(Boolean)
      : [];

  return {
    BUILD_PROPERTY_SLUGS: slugs.join(','),
    BUILD_BLOG_IDS: blogIds.join(','),
    STATIC_EXPORT: '1',
    NEXT_PUBLIC_BACKEND_URL: backendUrl,
  };
}

function copyRecursive(src, dest) {
  if (!fs.existsSync(src)) return;
  const stat = fs.statSync(src);
  if (stat.isDirectory()) {
    fs.mkdirSync(dest, { recursive: true });
    for (const entry of fs.readdirSync(src)) {
      copyRecursive(path.join(src, entry), path.join(dest, entry));
    }
    return;
  }
  fs.mkdirSync(path.dirname(dest), { recursive: true });
  fs.copyFileSync(src, dest);
}

function stashApiRoutes() {
  if (fs.existsSync(stashDir)) {
    fs.rmSync(stashDir, { recursive: true, force: true });
  }
  if (!fs.existsSync(apiDir)) return;
  fs.mkdirSync(stashDir, { recursive: true });
  const stashedApi = path.join(stashDir, 'api');
  try {
    fs.renameSync(apiDir, stashedApi);
  } catch (err) {
    if (err.code !== 'EPERM' && err.code !== 'EXDEV') throw err;
    copyRecursive(apiDir, stashedApi);
    fs.rmSync(apiDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
  }
  log('Stashed app/api (static export uses PHP /api/* instead)');
}

function restoreApiRoutes() {
  const stashed = path.join(stashDir, 'api');
  if (!fs.existsSync(stashed)) return;
  if (fs.existsSync(apiDir)) {
    fs.rmSync(apiDir, { recursive: true, force: true, maxRetries: 5, retryDelay: 200 });
  }
  try {
    fs.renameSync(stashed, apiDir);
  } catch (err) {
    if (err.code !== 'EPERM' && err.code !== 'EXDEV') throw err;
    copyRecursive(stashed, apiDir);
    fs.rmSync(stashed, { recursive: true, force: true });
  }
  log('Restored app/api');
}

function packageRelease() {
  if (fs.existsSync(releaseDir)) {
    fs.rmSync(releaseDir, { recursive: true, force: true });
  }
  fs.mkdirSync(releaseDir, { recursive: true });

  for (const item of ['application', 'system', 'assets', 'index.php', 'hook.php', 'promo_agent.png']) {
    const src = path.join(root, item);
    if (fs.existsSync(src)) {
      copyRecursive(src, path.join(releaseDir, item));
    }
  }

  const htaccessProd = path.join(root, 'deploy', 'htaccess.production');
  if (fs.existsSync(htaccessProd)) {
    fs.copyFileSync(htaccessProd, path.join(releaseDir, '.htaccess'));
    log('Applied production .htaccess (static Next + PHP API routes)');
  }

  const outDir = path.join(root, 'out');
  if (fs.existsSync(outDir)) {
    copyRecursive(outDir, releaseDir);
    log('Merged static Next export (out/) into release/');
    // Owner panel is PHP-only — drop static shells so Apache never serves Next.js for these URLs.
    for (const rel of [
      'owner/dashboard',
      'owner/listings',
      'owner/enquiries',
      'owner/site-visits',
      'owner/property',
    ]) {
      const stale = path.join(releaseDir, rel);
      if (fs.existsSync(stale)) {
        fs.rmSync(stale, { recursive: true, force: true });
        log(`Removed static ${rel}/ (PHP owner panel)`);
      }
    }
  }

  fs.mkdirSync(path.join(releaseDir, 'assets', 'uploads', 'feedbacks'), { recursive: true });

  log(`Release ready: ${releaseDir}`);
}

/** Copy static Next export + production .htaccess to repo root (PHP/assets already tracked). */
function stageReleaseToRoot() {
  const outDir = path.join(root, 'out');
  if (!fs.existsSync(outDir)) {
    log('Nothing to stage — run build first (out/ missing)');
    return;
  }

  const htaccessProd = path.join(root, 'deploy', 'htaccess.production');
  if (fs.existsSync(htaccessProd)) {
    fs.copyFileSync(htaccessProd, path.join(root, '.htaccess'));
    log('Applied production .htaccess to repo root');
  }

  for (const entry of fs.readdirSync(outDir)) {
    // uploads/assets already live in repo — skip to save disk during staging
    if (entry === 'assets' || entry === 'uploads') continue;
    copyRecursive(path.join(outDir, entry), path.join(root, entry));
  }

  log('Staged static export from out/ → git add _next/ and HTML, commit, push, then deploy');
}

async function main() {
  const stageForGit = process.argv.includes('--git') || process.env.STAGE_FOR_GIT === '1';
  const buildEnv = await loadBuildParams();

  stashApiRoutes();
  try {
    run('npm run build', buildEnv);
    if (stageForGit) {
      stageReleaseToRoot();
    } else {
      packageRelease();
    }
  } finally {
    restoreApiRoutes();
  }
}

main().catch((err) => {
  console.error(err);
  restoreApiRoutes();
  process.exit(1);
});
