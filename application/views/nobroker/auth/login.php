<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:420px">
  <h1 class="h3 mb-4">Login</h1>
  <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
  <?php echo form_open('login'); ?>
    <div class="mb-3">
      <label class="form-label">Email or phone</label>
      <input type="text" name="login" class="form-control" value="<?php echo set_value('login'); ?>" required autocomplete="username" placeholder="you@example.com or 9876543210">
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-danger w-100">Login</button>
  <?php echo form_close(); ?>
  <p class="mt-3 small text-center"><a href="<?php echo base_url(); ?>?modal=register">Create account</a></p>
</div>
