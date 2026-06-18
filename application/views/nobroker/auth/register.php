<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container py-5" style="max-width:520px">
  <h1 class="h3 mb-4">Register as property owner</h1>
  <?php echo validation_errors('<div class="alert alert-danger">', '</div>'); ?>
  <?php echo form_open('register'); ?>
    <div class="mb-3">
      <label class="form-label">Full name</label>
      <input type="text" name="name" class="form-control" value="<?php echo set_value('name'); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?php echo set_value('email'); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Phone</label>
      <input type="text" name="phone" class="form-control" value="<?php echo set_value('phone'); ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm password</label>
      <input type="password" name="password2" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">City</label>
      <select name="city_id" class="form-select">
        <option value="">—</option>
        <?php foreach ($cities as $c) : ?>
          <option value="<?php echo (int) $c->id; ?>"><?php echo html_escape($c->name); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" name="terms" value="1" class="form-check-input" id="terms" required>
      <label class="form-check-label" for="terms">I agree to the terms</label>
    </div>
    <button type="submit" class="btn btn-danger w-100">Register</button>
  <?php echo form_close(); ?>
</div>
