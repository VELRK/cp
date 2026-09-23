<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$is_edit = !empty($is_edit);
$row = isset($row) && is_array($row) ? $row : array();
$event_options = isset($event_options) && is_array($event_options) ? $event_options : array();
$audience_options = isset($audience_options) && is_array($audience_options) ? $audience_options : array();
$catalog = isset($catalog) && is_array($catalog) ? $catalog : array();
$is_system = !empty($row['is_system']);
$event_key = isset($row['event_key']) ? $row['event_key'] : 'enquiry';
$audience = isset($row['audience']) ? $row['audience'] : 'admin';
$placeholders = array();
foreach ($catalog as $ek => $group) {
    $placeholders[$ek] = isset($group['placeholders']) ? $group['placeholders'] : '';
}
$action = $is_edit
    ? site_url('panel/settings/templates/edit/' . rawurlencode($row['template_key']))
    : site_url('panel/settings/templates/create');
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title"><?php echo $is_edit ? 'Edit email template' : 'Create email template'; ?></h1>
    <p class="nb-admin-page-desc mb-0">Choose the operation, who receives it, then set subject and body.</p>
  </div>
  <a class="btn btn-outline-secondary rounded-pill px-3" href="<?php echo site_url('panel/settings/templates'); ?>">
    <i class="bi bi-arrow-left me-1"></i> Back
  </a>
</div>

<ul class="nav nav-pills gap-2 mb-4">
  <li class="nav-item">
    <a class="nav-link rounded-pill" href="<?php echo site_url('panel/settings'); ?>">
      <i class="bi bi-hdd-network me-1"></i> Mail details
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link rounded-pill active" href="<?php echo site_url('panel/settings/templates'); ?>">
      <i class="bi bi-envelope-paper me-1"></i> Email templates
    </a>
  </li>
</ul>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-body p-4">
    <?php echo form_open($action); ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbTplLabel">Name</label>
          <input type="text" class="form-control nb-admin-input" id="nbTplLabel" name="label" required maxlength="120"
            value="<?php echo html_escape(isset($row['label']) ? $row['label'] : ''); ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbTplEvent">Operation</label>
          <select class="form-select nb-admin-input" id="nbTplEvent" name="event_key" <?php echo $is_system ? 'disabled' : ''; ?>>
            <?php foreach ($event_options as $ek => $elabel) : ?>
              <option value="<?php echo html_escape($ek); ?>" <?php echo $event_key === $ek ? 'selected' : ''; ?>>
                <?php echo html_escape($elabel); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($is_system) : ?>
            <input type="hidden" name="event_key" value="<?php echo html_escape($event_key); ?>">
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbTplAudience">Send to</label>
          <select class="form-select nb-admin-input" id="nbTplAudience" name="audience" <?php echo $is_system ? 'disabled' : ''; ?>>
            <?php foreach ($audience_options as $ak => $alabel) : ?>
              <option value="<?php echo html_escape($ak); ?>" <?php echo $audience === $ak ? 'selected' : ''; ?>>
                <?php echo html_escape($alabel); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <?php if ($is_system) : ?>
            <input type="hidden" name="audience" value="<?php echo html_escape($audience); ?>">
          <?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" for="nbTplTo">Override email (optional)</label>
          <input type="email" class="form-control nb-admin-input" id="nbTplTo" name="to_email"
            value="<?php echo html_escape(isset($row['to_email']) ? $row['to_email'] : ''); ?>"
            placeholder="Leave blank to use the default recipient">
          <div class="form-text">Use this to send a copy to another address.</div>
        </div>
        <div class="col-md-8">
          <label class="form-label fw-semibold" for="nbTplSubject">Subject</label>
          <input type="text" class="form-control nb-admin-input" id="nbTplSubject" name="subject" required
            value="<?php echo html_escape(isset($row['subject']) ? $row['subject'] : ''); ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" name="is_enabled" value="1" id="nbTplEnabled"
              <?php echo !empty($row['is_enabled']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="nbTplEnabled">Send this email</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold" for="nbTplHeading">Heading</label>
          <input type="text" class="form-control nb-admin-input" id="nbTplHeading" name="heading"
            value="<?php echo html_escape(isset($row['heading']) ? $row['heading'] : ''); ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold" for="nbTplBody">Body</label>
          <textarea class="form-control nb-admin-input" id="nbTplBody" name="body" rows="10"><?php echo html_escape(isset($row['body']) ? $row['body'] : ''); ?></textarea>
          <div class="form-text">Placeholders: <code id="nbTplPlaceholders"><?php echo html_escape(isset($placeholders[$event_key]) ? $placeholders[$event_key] : ''); ?></code></div>
        </div>
      </div>
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-success rounded-pill px-4"><?php echo $is_edit ? 'Save template' : 'Create template'; ?></button>
        <a class="btn btn-outline-secondary rounded-pill px-4" href="<?php echo site_url('panel/settings/templates'); ?>">Cancel</a>
      </div>
    <?php echo form_close(); ?>
  </div>
</div>
<script>
(function () {
  var map = <?php echo json_encode($placeholders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  var sel = document.getElementById('nbTplEvent');
  var out = document.getElementById('nbTplPlaceholders');
  if (!sel || !out) return;
  sel.addEventListener('change', function () {
    out.textContent = map[sel.value] || '';
  });
})();
</script>
