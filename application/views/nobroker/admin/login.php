<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Coimbatore Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #0b2c56 0%, #164a7a 55%, #1a6b8a 100%);
      padding: 1.5rem;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
      padding: 2.25rem;
    }
    .brand-badge {
      width: 52px;
      height: 52px;
      border-radius: 12px;
      background: rgba(11, 44, 86, 0.08);
      color: #0b2c56;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
    }
    .btn-admin {
      background: #0b2c56;
      border-color: #0b2c56;
      font-weight: 600;
    }
    .btn-admin:hover {
      background: #083052;
      border-color: #083052;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="brand-badge mb-3"><i class="bi bi-shield-lock"></i></div>
      <h1 class="h4 fw-bold text-dark mb-1">Admin Login</h1>
      <p class="text-muted small mb-0">Coimbatore Properties control panel</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 small"><?php echo html_escape($error); ?></div>
    <?php endif; ?>

    <?php echo form_open(site_url('admin/login')); ?>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Email or phone</label>
        <input type="text" name="login" class="form-control" value="<?php echo html_escape(set_value('login')); ?>" required autofocus autocomplete="username" placeholder="admin@example.com">
      </div>
      <div class="mb-4">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn-primary btn-admin w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign in to Admin Panel
      </button>
    <?php echo form_close(); ?>

    <p class="text-center text-muted small mt-3 mb-0">
      <a href="<?php echo site_url(); ?>" class="text-decoration-none">← Back to website</a>
    </p>
  </div>
</body>
</html>
