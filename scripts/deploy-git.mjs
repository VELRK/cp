#!/usr/bin/env node
/**
 * Pull latest commit from GitHub onto the server (git only — no build, no rsync).
 *
 *   npm run deploy
 *
 * Run after: npm run build:git → git push origin main
 */

import { execFileSync } from 'node:child_process';
import { loadDeployConfig, sshBaseArgs } from './deploy-config.mjs';

function log(msg) {
  console.log(`[deploy-git] ${msg}`);
}

function remoteGitScript(cfg) {
  const target = cfg.target.replace(/\/$/, '');
  const repo = cfg.repo;
  const branch = cfg.branch;
  return `
set -e
TARGET="${target}"
REPO="${repo}"
BRANCH="${branch}"
mkdir -p "$TARGET"
cd "$TARGET"
if [ ! -d ".git" ]; then
  git init
  git remote add origin "$REPO"
else
  git remote set-url origin "$REPO" 2>/dev/null || git remote add origin "$REPO"
fi
git fetch origin "$BRANCH"
git checkout -B "$BRANCH" "origin/$BRANCH" 2>/dev/null || git reset --hard "origin/$BRANCH"
git reset --hard "origin/$BRANCH"
echo "Deployed commit: $(git rev-parse --short HEAD) from $REPO"
`.trim();
}

function main() {
  const cfg = loadDeployConfig();
  if (!cfg.host || !cfg.user) {
    console.error('[deploy-git] Set ssh_host and ssh_user in deploy.local.php');
    process.exit(1);
  }

  log(`Pull ${cfg.repo} (${cfg.branch}) → ${cfg.user}@${cfg.host}:${cfg.target}`);

  const sshArgs = [...sshBaseArgs(cfg), `${cfg.user}@${cfg.host}`, remoteGitScript(cfg)];
  execFileSync('ssh', sshArgs, { stdio: 'inherit' });

  log('Git deploy complete.');
  log('Tip: upload deploy.local.php on the server once (gitignored, not in repo).');
}

main();
