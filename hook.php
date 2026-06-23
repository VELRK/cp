<?php
/**
 * GitHub Actions deploy webhook
 * Called by CI: GET /cp/hook.php?token=SECRET
 *
 * Requires this folder to be a git repo (Hostinger hPanel → GIT → connect GitHub).
 * Config: deploy.local.php (gitignored, upload manually to server).
 */
declare(strict_types=1);

function hook_load_config(): array
{
    $localFile = __DIR__ . '/deploy.local.php';
    if (is_readable($localFile)) {
        $config = require $localFile;
        return is_array($config) ? $config : [];
    }

    return [];
}

function hook_deploy_token(array $config): string
{
    $fromEnv = getenv('DEPLOY_TOKEN');
    if (is_string($fromEnv) && $fromEnv !== '') {
        return $fromEnv;
    }

    $fromFile = $config['deploy_token'] ?? '';
    return is_string($fromFile) ? $fromFile : '';
}

function hook_json(int $status, array $payload): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

$config = hook_load_config();
$secret = hook_deploy_token($config);
$provided = (string) ($_GET['token'] ?? '');
$localFile = __DIR__ . '/deploy.local.php';

if (!is_readable($localFile) && $secret === '') {
    hook_json(403, [
        'success' => false,
        'error'   => 'Forbidden',
        'reason'  => 'missing_config',
        'hint'    => 'Upload deploy.local.php to this folder on the server',
    ]);
}

if ($secret === '' || $secret === 'CHANGE_ME_TO_A_LONG_RANDOM_STRING') {
    hook_json(403, [
        'success' => false,
        'error'   => 'Forbidden',
        'reason'  => 'token_not_set',
        'hint'    => 'Edit deploy.local.php and set deploy_token',
    ]);
}

if ($provided === '' || !hash_equals($secret, $provided)) {
    hook_json(403, [
        'success' => false,
        'error'   => 'Forbidden',
        'reason'  => 'token_mismatch',
        'hint'    => 'URL ?token= must match deploy_token in deploy.local.php',
    ]);
}

// Pull in the same folder as hook.php unless target_path is set in deploy.local.php
$target = isset($config['target_path']) && is_string($config['target_path']) && $config['target_path'] !== ''
    ? $config['target_path']
    : __DIR__;
$branch = isset($config['git_branch']) && is_string($config['git_branch']) && $config['git_branch'] !== ''
    ? $config['git_branch']
    : 'main';

if (!is_dir($target)) {
    hook_json(500, [
        'success' => false,
        'error'   => 'target_not_found',
        'target'  => $target,
        'hint'    => 'Fix target_path in deploy.local.php or remove it to use the hook.php folder',
    ]);
}

if (!is_dir($target . '/.git')) {
    hook_json(500, [
        'success' => false,
        'error'   => 'not_git_repo',
        'target'  => $target,
        'hint'    => 'Hostinger hPanel → GIT → connect your GitHub repo to this folder (public_html/cp)',
        'steps'   => [
            'Open Hostinger → GIT',
            'Add repository with your GitHub repo URL',
            'Branch: main',
            'Install/deploy path: public_html/cp',
            'If folder has old FTP files, back them up first then let GIT clone fresh',
        ],
    ]);
}

$output = [];
$cmd = 'cd ' . escapeshellarg($target) . ' && git pull origin ' . escapeshellarg($branch) . ' 2>&1';
exec($cmd, $output, $code);

hook_json($code === 0 ? 200 : 500, [
    'success' => $code === 0,
    'target'  => $target,
    'branch'  => $branch,
    'output'  => implode("\n", $output),
]);
