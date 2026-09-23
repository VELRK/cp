<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$tab = isset($settings_tab) ? $settings_tab : 'mail';
$mail = isset($mail) && is_array($mail) ? $mail : array();
$firebase = isset($firebase) && is_array($firebase) ? $firebase : array();
$catalog = isset($catalog) && is_array($catalog) ? $catalog : array();
$templates = isset($templates) && is_array($templates) ? $templates : array();
$has_smtp_pass = !empty($mail['smtp_pass']);
$has_vapid = !empty($firebase_has_vapid);
$has_service_account = !empty($firebase_has_service_account);
$firebase_sa_project = isset($firebase_sa_project) ? (string) $firebase_sa_project : '';
$page_desc = 'SMTP and From address used for enquiry, KYC, and property emails.';
if ($tab === 'templates') {
  $page_desc = 'Create and edit the subject and body for each mail. Placeholders like {name} and {title} are replaced when the email is sent.';
} elseif ($tab === 'firebase') {
  $page_desc = 'Web keys, VAPID, and service account JSON used to send push notifications to owners, agents, tenants, and admins.';
}
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title"><?php echo $tab === 'templates' ? 'Email templates' : 'Settings'; ?></h1>
    <p class="nb-admin-page-desc mb-0"><?php echo $page_desc; ?></p>
  </div>
  <?php if ($tab === 'templates') : ?>
  <a class="btn btn-success rounded-pill px-3" href="<?php echo site_url('panel/settings/templates/create'); ?>">
    <i class="bi bi-plus-lg me-1"></i> Create template
  </a>
  <?php endif; ?>
</div>

<ul class="nav nav-pills gap-2 mb-4">
  <li class="nav-item">
    <a class="nav-link rounded-pill<?php echo $tab === 'mail' ? ' active' : ''; ?>" href="<?php echo site_url('panel/settings'); ?>">
      <i class="bi bi-hdd-network me-1"></i> Mail details
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link rounded-pill<?php echo $tab === 'firebase' ? ' active' : ''; ?>" href="<?php echo site_url('panel/settings/firebase'); ?>">
      <i class="bi bi-bell me-1"></i> Firebase
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link rounded-pill<?php echo $tab === 'templates' ? ' active' : ''; ?>" href="<?php echo site_url('panel/settings/templates'); ?>">
      <i class="bi bi-envelope-paper me-1"></i> Email templates
    </a>
  </li>
</ul>

<?php if ($tab === 'firebase') : ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Firebase Cloud Messaging</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
        <?php
        $web_project = isset($firebase['project_id']) ? trim((string) $firebase['project_id']) : '';
        if ($firebase_sa_project !== '' && $web_project !== '' && $firebase_sa_project !== $web_project) :
        ?>
        <div class="alert alert-warning">
          The saved service account is for Firebase project <strong><?php echo html_escape($firebase_sa_project); ?></strong>,
          but the web config uses <strong><?php echo html_escape($web_project); ?></strong>.
          Paste a new private key from the matching project or push will fail.
        </div>
        <?php endif; ?>
        <?php echo form_open(site_url('panel/settings/firebase')); ?>
          <input type="hidden" name="settings_action" value="firebase">
          <h3 class="h6 fw-semibold mb-3">Web app config</h3>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbApiKey">API key</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbApiKey" name="api_key"
                value="<?php echo html_escape(isset($firebase['api_key']) ? $firebase['api_key'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbAuthDomain">Auth domain</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbAuthDomain" name="auth_domain"
                value="<?php echo html_escape(isset($firebase['auth_domain']) ? $firebase['auth_domain'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbProjectId">Project ID</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbProjectId" name="project_id"
                value="<?php echo html_escape(isset($firebase['project_id']) ? $firebase['project_id'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbBucket">Storage bucket</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbBucket" name="storage_bucket"
                value="<?php echo html_escape(isset($firebase['storage_bucket']) ? $firebase['storage_bucket'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbSender">Messaging sender ID</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbSender" name="messaging_sender_id"
                value="<?php echo html_escape(isset($firebase['messaging_sender_id']) ? $firebase['messaging_sender_id'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbAppId">App ID</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbAppId" name="app_id"
                value="<?php echo html_escape(isset($firebase['app_id']) ? $firebase['app_id'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbMeasurement">Measurement ID</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbMeasurement" name="measurement_id"
                value="<?php echo html_escape(isset($firebase['measurement_id']) ? $firebase['measurement_id'] : ''); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold" for="nbFbVapid">Web Push certificate (VAPID)</label>
              <input type="text" class="form-control nb-admin-input" id="nbFbVapid" name="vapid_key" autocomplete="off"
                placeholder="<?php echo $has_vapid ? 'Leave blank to keep the saved key' : 'Key pair from Cloud Messaging'; ?>"
                value="<?php echo html_escape(isset($firebase['vapid_key']) ? $firebase['vapid_key'] : ''); ?>">
              <?php if ($has_vapid) : ?>
              <div class="form-text">A VAPID key is already saved. Paste a new one only if you are replacing it.</div>
              <?php endif; ?>
            </div>
          </div>
          <h3 class="h6 fw-semibold mt-4 mb-3">Service account (server send)</h3>
          <div class="mb-3">
            <label class="form-label fw-semibold" for="nbFbServiceAccount">Service account JSON</label>
            <textarea class="form-control nb-admin-input font-monospace" id="nbFbServiceAccount" name="service_account_json" rows="8"
              placeholder="<?php echo $has_service_account ? 'Leave blank to keep the saved service account' : 'Paste the full JSON file here'; ?>"></textarea>
            <div class="form-text">
              <?php echo $has_service_account ? 'A service account is already saved in the database.' : 'Paste the full JSON file from Firebase Console → Project settings → Service accounts → Generate new private key.'; ?>
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-success rounded-pill px-4">Save Firebase settings</button>
          </div>
        <?php echo form_close(); ?>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="nb-admin-panel">
      <div class="nb-admin-panel-header">
        <h2 class="nb-admin-panel-title mb-0">Where to copy this</h2>
      </div>
      <div class="nb-admin-panel-body p-4">
        <ol class="small text-muted mb-0 ps-3">
          <li class="mb-2">Firebase Console → project <strong>coimbatore-property</strong> → Project settings → Your apps → Web app config.</li>
          <li class="mb-2">Cloud Messaging → Web Push certificates → Generate key pair (VAPID).</li>
          <li>Service accounts → Generate new private key. Paste the JSON here. Do not use a key from another Firebase project.</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<?php elseif ($tab === 'mail') : ?>
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
    <h2 class="nb-admin-panel-title mb-0">Templates</h2>
    <span class="badge bg-light text-dark border"><?php echo count($templates); ?> total</span>
  </div>
  <div class="nb-admin-panel-body">
    <div class="nb-admin-table-wrap">
      <table class="table nb-admin-table mb-0">
        <thead>
          <tr>
            <th>Name</th>
            <th>Operation</th>
            <th>Send to</th>
            <th>Subject</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($templates)) : ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-5">
              No email templates yet. Click <strong>Create template</strong> to add one.
            </td>
          </tr>
        <?php else : foreach ($templates as $row) : ?>
          <tr>
            <td>
              <strong><?php echo html_escape($row['label']); ?></strong>
              <?php if (empty($row['is_system'])) : ?>
                <span class="badge bg-light text-dark border ms-1">Custom</span>
              <?php endif; ?>
            </td>
            <td><?php echo html_escape($row['event_label']); ?></td>
            <td>
              <?php echo html_escape($row['audience_label']); ?>
              <?php if (!empty($row['to_email'])) : ?>
                <div class="small text-muted"><?php echo html_escape($row['to_email']); ?></div>
              <?php endif; ?>
            </td>
            <td class="text-muted"><?php echo html_escape($row['subject']); ?></td>
            <td>
              <?php if (!empty($row['is_enabled'])) : ?>
                <span class="badge bg-success">On</span>
              <?php else : ?>
                <span class="badge bg-secondary">Off</span>
              <?php endif; ?>
            </td>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-primary rounded-pill" href="<?php echo site_url('panel/settings/templates/edit/' . rawurlencode($row['template_key'])); ?>">Edit</a>
              <?php if (empty($row['is_system'])) : ?>
                <a class="btn btn-sm btn-outline-danger rounded-pill" href="<?php echo site_url('panel/settings/templates/delete/' . rawurlencode($row['template_key'])); ?>"
                  onclick="return confirm('Delete this email template?');">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
