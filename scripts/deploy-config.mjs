import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
export const projectRoot = path.resolve(__dirname, '..');

export function parsePhpConfig(filePath) {
  if (!fs.existsSync(filePath)) return {};
  const text = fs.readFileSync(filePath, 'utf8');
  const get = (key) => {
    const m = text.match(new RegExp(`['"]${key}['"]\\s*=>\\s*['"]([^'"]*)['"]`));
    return m ? m[1] : '';
  };
  const getNum = (key) => {
    const m = text.match(new RegExp(`['"]${key}['"]\\s*=>\\s*(\\d+)`));
    return m ? Number(m[1]) : 0;
  };
  return {
    ssh_host: get('ssh_host'),
    ssh_user: get('ssh_user'),
    ssh_port: getNum('ssh_port') || 65002,
    ssh_key: get('ssh_key'),
    target_path: get('target_path'),
    git_repo: get('git_repo'),
    git_branch: get('git_branch') || 'main',
  };
}

export function loadDeployConfig() {
  const fromFile = parsePhpConfig(path.join(projectRoot, 'deploy.local.php'));
  return {
    host: process.env.DEPLOY_SSH_HOST || fromFile.ssh_host,
    user: process.env.DEPLOY_SSH_USER || fromFile.ssh_user,
    port: Number(process.env.DEPLOY_SSH_PORT || fromFile.ssh_port || 65002),
    key: process.env.DEPLOY_SSH_KEY || fromFile.ssh_key,
    target:
      process.env.DEPLOY_TARGET ||
      fromFile.target_path ||
      '/home/u221026474/domains/superfinelabels.in/public_html/cp',
    repo:
      process.env.DEPLOY_GIT_REPO ||
      fromFile.git_repo ||
      'https://github.com/VELRK/cp.git',
    branch: process.env.DEPLOY_GIT_BRANCH || fromFile.git_branch || 'main',
  };
}

export function sshBaseArgs(cfg) {
  const args = ['-p', String(cfg.port), '-o', 'StrictHostKeyChecking=accept-new'];
  if (cfg.key) {
    args.push('-i', cfg.key);
  }
  return args;
}
