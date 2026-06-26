#!/usr/bin/env node
/**
 * Build + rsync deploy/release/ to Hostinger (same as GitHub Actions deploy job).
 *
 *   BACKEND_URL=https://superfinelabels.in/cp npm run deploy:rsync
 *
 * Requires: Node.js, rsync, SSH key in deploy.local.php
 */

import { execFileSync, execSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadDeployConfig, projectRoot, sshBaseArgs } from './deploy-config.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

function log(msg) {
  console.log(`[deploy-rsync] ${msg}`);
}

function backupSecretsScript(cfg) {
  const target = cfg.target.replace(/\/$/, '');
  return `
set -e
TARGET="${target}"
BACKUP_DIR=/tmp/cp-deploy-backup-latest
rm -rf "$BACKUP_DIR"
mkdir -p "$BACKUP_DIR"
cd "$TARGET"
[ -f application/config/database.php ] && cp application/config/database.php "$BACKUP_DIR/database.php"
[ -f application/config/whatsapp.local.php ] && cp application/config/whatsapp.local.php "$BACKUP_DIR/whatsapp.local.php"
[ -f .env ] && cp .env "$BACKUP_DIR/.env"
[ -f deploy.local.php ] && cp deploy.local.php "$BACKUP_DIR/deploy.local.php"
echo "Secrets backed up to $BACKUP_DIR"
`.trim();
}

function restoreSecretsScript(cfg) {
  const target = cfg.target.replace(/\/$/, '');
  return `
set -e
TARGET="${target}"
BACKUP_DIR=/tmp/cp-deploy-backup-latest
cd "$TARGET"
[ -f "$BACKUP_DIR/database.php" ] && cp "$BACKUP_DIR/database.php" application/config/database.php
if [ ! -f application/config/database.php ] && [ -f application/config/database.php.example ]; then
  cp application/config/database.php.example application/config/database.php
fi
[ -f "$BACKUP_DIR/whatsapp.local.php" ] && cp "$BACKUP_DIR/whatsapp.local.php" application/config/whatsapp.local.php
[ -f "$BACKUP_DIR/.env" ] && cp "$BACKUP_DIR/.env" .env
[ -f "$BACKUP_DIR/deploy.local.php" ] && cp "$BACKUP_DIR/deploy.local.php" deploy.local.php
rm -rf "$BACKUP_DIR"
echo "Secrets restored at $TARGET"
`.trim();
}

function main() {
  const cfg = loadDeployConfig();
  if (!cfg.host || !cfg.user) {
    console.error('[deploy-rsync] Set ssh_host and ssh_user in deploy.local.php');
    process.exit(1);
  }

  const backendUrl = (process.env.BACKEND_URL || 'https://superfinelabels.in/cp').replace(/\/$/, '');
  log(`Building release (BACKEND_URL=${backendUrl})…`);
  execSync('node scripts/build-deploy.mjs', {
    cwd: projectRoot,
    stdio: 'inherit',
    env: { ...process.env, BACKEND_URL: backendUrl },
  });

  const releaseDir = path.join(projectRoot, 'deploy', 'release');
  if (!fs.existsSync(releaseDir)) {
    console.error('[deploy-rsync] deploy/release/ missing after build');
    process.exit(1);
  }

  const excludeFile = path.join(projectRoot, 'deploy', 'rsync-exclude.txt');
  const target = `${cfg.user}@${cfg.host}:${cfg.target.replace(/\/$/, '')}/`;

  log(`Backup secrets on ${cfg.host}…`);
  execFileSync(
    'ssh',
    [...sshBaseArgs(cfg), `${cfg.user}@${cfg.host}`, backupSecretsScript(cfg)],
    { stdio: 'inherit' }
  );

  log(`Rsync → ${target}`);
  const rsyncArgs = [
    '-avz',
    '--delete',
    '-e',
    `ssh -p ${cfg.port} ${cfg.key ? `-i ${cfg.key}` : ''} -o StrictHostKeyChecking=accept-new`.trim(),
    `--exclude-from=${excludeFile}`,
    `${releaseDir}/`,
    target,
  ];
  execFileSync('rsync', rsyncArgs, { stdio: 'inherit' });

  log('Restore secrets…');
  execFileSync(
    'ssh',
    [...sshBaseArgs(cfg), `${cfg.user}@${cfg.host}`, restoreSecretsScript(cfg)],
    { stdio: 'inherit' }
  );

  log('Rsync deploy complete.');
}

main();
