<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$tab = isset($settings_tab) ? $settings_tab : 'mail';
$mail = isset($mail) && is_array($mail) ? $mail : array();
$catalog = isset($catalog) && is_array($catalog) ? $catalog : array();
$templates = isset($templates) && is_array($templates) ? $templates : array();
$has_smtp_pass = !empty($mail['smtp_pass']);
?>
<div class="nb-admin-page-head">
  <h1 class="nb-admin-page-title"><?php echo $tab === 'templates' ? 'Email templates' : 'Settings'; ?></h1>
  <p class="nb-admin-page-desc mb-0">
    <?php echo $tab === 'templates'
      ? 'Configure the subject and body for each mail operation. Placeholders like {name} and {title} are replaced when the email is sent.'
      : 'SMTP and From address used for enquiry, KYC, and property emails.'; ?>
  </p>
</div>

<ul class="nav nav-pills gap-2 mb-4">
  <li class="nav-item">
    <a class="nav-link rounded-pill<?php echo $tab === 'mail' ? ' active' : ''; ?>" href="<?php echo site_url('panel/settings'); ?>">
      <i class="bi bi-hdd-network me-1"></i> Mail details
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link rounded-pill<?php echo $tab === 'templates' ? ' active' : ''; ?>" href="<?php echo site_url('panel/settings/templates'); ?>">
      <i class="bi bi-envelope-paper me-1"></i> Email templates
    </a>
  </li>
</ul>

<?php if ($tab === 'mail') : ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Mail details</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
        <?php echo form_open(site_url('panel/settings')); ?>
          <input type="hidden" name="settings_action" value="mail">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbMailAdmin">Admin inbox</label>
              <input type="email" class="form-control nb-admin-input" id="nbMailAdmin" name="admin_email" required
                value="<?php echo html_escape(isset($mail['admin_email']) ? $mail['admin_email'] : ''); ?>">
              <div class="form-text">Receives enquiry, KYC, and listing notifications.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbMailFromName">From name</label>
              <input type="text" class="form-control nb-admin-input" id="nbMailFromName" name="from_name" maxlength="120"
                value="<?php echo html_escape(isset($mail['from_name']) ? $mail['from_name'] : 'Coimbatore Properties'); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbMailFrom">From email</label>
              <input type="email" class="form-control nb-admin-input" id="nbMailFrom" name="from_email"
                value="<?php echo html_escape(isset($mail['from_email']) ? $mail['from_email'] : ''); ?>"
                placeholder="mailbox@yourdomain.com">
              <div class="form-text">Must match your SMTP mailbox on Hostinger.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbSmtpHost">SMTP host</label>
              <input type="text" class="form-control nb-admin-input" id="nbSmtpHost" name="smtp_host"
                value="<?php echo html_escape(isset($mail['smtp_host']) ? $mail['smtp_host'] : ''); ?>"
                placeholder="smtp.hostinger.com">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="nbSmtpPort">SMTP port</label>
              <input type="number" class="form-control nb-admin-input" id="nbSmtpPort" name="smtp_port"
                value="<?php echo html_escape(isset($mail['smtp_port']) ? $mail['smtp_port'] : '465'); ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="nbSmtpCrypto">Encryption</label>
              <select class="form-select nb-admin-input" id="nbSmtpCrypto" name="smtp_crypto">
                <?php $crypto = isset($mail['smtp_crypto']) ? $mail['smtp_crypto'] : 'ssl'; ?>
                <option value="ssl" <?php echo $crypto === 'ssl' ? 'selected' : ''; ?>>SSL (465)</option>
                <option value="tls" <?php echo $crypto === 'tls' ? 'selected' : ''; ?>>TLS (587)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold" for="nbSmtpUser">SMTP username</label>
              <input type="text" class="form-control nb-admin-input" id="nbSmtpUser" name="smtp_user" autocomplete="off"
                value="<?php echo html_escape(isset($mail['smtp_user']) ? $mail['smtp_user'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbSmtpPass">SMTP password</label>
              <input type="password" class="form-control nb-admin-input" id="nbSmtpPass" name="smtp_pass" autocomplete="new-password"
                placeholder="<?php echo $has_smtp_pass ? 'Leave blank to keep the saved password' : 'SMTP password'; ?>">
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-success rounded-pill px-4">Save mail details</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Send test email</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
        <p class="small text-muted">Save mail details first, then send a test to confirm SMTP.</p>
        <?php echo form_open(site_url('panel/settings')); ?>
          <input type="hidden" name="settings_action" value="test">
          <label class="form-label fw-semibold" for="nbTestEmail">Send to</label>
          <input type="email" class="form-control nb-admin-input mb-3" id="nbTestEmail" name="test_email" required
            value="<?php echo html_escape(isset($mail['admin_email']) ? $mail['admin_email'] : ''); ?>">
          <button type="submit" class="btn btn-outline-primary rounded-pill px-4">Send test</button>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
</div>
<?php else : ?>
<div class="nb-admin-panel">
  <div class="nb-admin-panel-header">
    <h2 class="nb-admin-panel-title mb-0">Templates by operation</h2>
  </div>
  <div class="nb-admin-panel-body p-4">
    <?php echo form_open(site_url('panel/settings/templates')); ?>
      <input type="hidden" name="settings_action" value="templates">
      <div class="accordion" id="nbMailTplAcc">
        <?php $gi = 0; foreach ($catalog as $group_key => $group) : $gi++; ?>
        <?php
          $heading_id = 'nbTplH' . $gi;
          $collapse_id = 'nbTplC' . $gi;
        ?>
        <div class="accordion-item mb-2 border rounded-3 overflow-hidden">
          <h2 class="accordion-header" id="<?php echo html_escape($heading_id); ?>">
            <button class="accordion-button<?php echo $gi > 1 ? ' collapsed' : ''; ?>" type="button" data-bs-toggle="collapse"
              data-bs-target="#<?php echo html_escape($collapse_id); ?>" aria-expanded="<?php echo $gi === 1 ? 'true' : 'false'; ?>"
              aria-controls="<?php echo html_escape($collapse_id); ?>">
              <?php echo html_escape($group['label']); ?>
            </button>
          </h2>
          <div id="<?php echo html_escape($collapse_id); ?>" class="accordion-collapse collapse<?php echo $gi === 1 ? ' show' : ''; ?>"
            aria-labelledby="<?php echo html_escape($heading_id); ?>" data-bs-parent="#nbMailTplAcc">
            <div class="accordion-body">
              <p class="small text-muted">Placeholders: <code><?php echo html_escape($group['placeholders']); ?></code></p>
              <?php foreach ($group['templates'] as $key => $def) :
                $tpl = isset($templates[$key]) ? $templates[$key] : $def;
                $enabled = !empty($tpl['is_enabled']);
              ?>
              <div class="border rounded-3 p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <strong><?php echo html_escape($def['label']); ?></strong>
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" name="tpl[<?php echo html_escape($key); ?>][is_enabled]" value="1" id="en_<?php echo html_escape($key); ?>" <?php echo $enabled ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="en_<?php echo html_escape($key); ?>">Send this email</label>
                  </div>
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-semibold">Subject</label>
                  <input type="text" class="form-control nb-admin-input" name="tpl[<?php echo html_escape($key); ?>][subject]"
                    value="<?php echo html_escape($tpl['subject']); ?>">
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-semibold">Heading</label>
                  <input type="text" class="form-control nb-admin-input" name="tpl[<?php echo html_escape($key); ?>][heading]"
                    value="<?php echo html_escape($tpl['heading']); ?>">
                </div>
                <div>
                  <label class="form-label small fw-semibold">Body</label>
                  <textarea class="form-control nb-admin-input" name="tpl[<?php echo html_escape($key); ?>][body]" rows="7"><?php echo html_escape($tpl['body']); ?></textarea>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-success rounded-pill px-4">Save templates</button>
      </div>
    <?php echo form_close(); ?>
  </div>
</div>
<?php endif; ?>
