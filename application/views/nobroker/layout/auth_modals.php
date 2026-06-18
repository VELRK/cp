<?php defined('BASEPATH') OR exit('No direct script access allowed');
$modal_cities = isset($modal_cities) && is_array($modal_cities) ? $modal_cities : array();
?>
<div class="modal fade" id="nbModalLogin" tabindex="-1" aria-labelledby="nbModalLoginLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h2 class="modal-title h5 fw-semibold" id="nbModalLoginLabel">Login</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <?php echo form_open('login', array('id' => 'nb-login-form')); ?>
          <div id="nb-login-alert" class="alert d-none" role="alert"></div>
          <div class="mb-3">
            <label class="form-label" for="nb-modal-login-email">Email or phone</label>
            <input type="text" name="login" id="nb-modal-login-email" class="form-control" value="<?php echo set_value('login'); ?>" required autocomplete="username" placeholder="you@example.com or 9876543210">
          </div>
          <div class="mb-3">
            <label class="form-label" for="nb-modal-login-password">Password</label>
            <input type="password" name="password" id="nb-modal-login-password" class="form-control" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-danger w-100">Login</button>
        <?php echo form_close(); ?>
        <p class="mt-3 mb-0 small text-center text-secondary">
          <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Create account</button>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="nbModalRegister" tabindex="-1" aria-labelledby="nbModalRegisterLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h2 class="modal-title h5 fw-semibold" id="nbModalRegisterLabel">Register</h2>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2">
        <style>
          .nb-user-type-wrap { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
          .nb-user-type-item { position: relative; }
          .nb-user-type-check { position: absolute; opacity: 0; pointer-events: none; }
          .nb-user-type-label {
            display: block;
            border: 1px solid #d7dee7;
            border-radius: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            cursor: pointer;
            transition: all .2s ease;
            user-select: none;
            text-align: center;
            font-weight: 600;
          }
          .nb-user-type-label small { display: block; font-weight: 500; color: #6b7280; }
          .nb-user-type-check:checked + .nb-user-type-label {
            border-color: #198754;
            background: #e8f7f1;
            box-shadow: 0 0 0 2px rgba(25,135,84,.15);
            color: #0f5132;
          }
        </style>
        <?php echo form_open_multipart('register', array('id' => 'nb-register-form')); ?>
          <div id="nb-register-alert" class="alert d-none" role="alert"></div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label d-block">Register mode</label>
              <input type="hidden" name="user_type" id="nb-modal-reg-user-type" value="<?php echo set_value('user_type', 'customer'); ?>">
              <div class="nb-user-type-wrap">
                <div class="nb-user-type-item">
                  <input type="checkbox" id="nb-type-customer" class="nb-user-type-check" <?php echo set_checkbox('user_type', 'customer', set_value('user_type', 'customer') !== 'agent'); ?>>
                  <label for="nb-type-customer" class="nb-user-type-label">
                    Customer
                    <small>Buy / rent — no admin review</small>
                  </label>
                </div>
                <div class="nb-user-type-item">
                  <input type="checkbox" id="nb-type-agent" class="nb-user-type-check" <?php echo set_checkbox('user_type', 'agent', set_value('user_type') === 'agent'); ?>>
                  <label for="nb-type-agent" class="nb-user-type-label">
                    Agent
                    <small>List properties — account is approved on registration</small>
                  </label>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-name">Full name</label>
              <input type="text" name="name" id="nb-modal-reg-name" class="form-control" value="<?php echo set_value('name'); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-email">Email</label>
              <input type="email" name="email" id="nb-modal-reg-email" class="form-control" value="<?php echo set_value('email'); ?>" required autocomplete="email">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-phone">Phone</label>
              <input type="text" name="phone" id="nb-modal-reg-phone" class="form-control" value="<?php echo set_value('phone'); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-city">City</label>
              <select name="city_id" id="nb-modal-reg-city" class="form-select">
                <option value="0">Select city</option>
                <?php foreach ($modal_cities as $c) : ?>
                  <option value="<?php echo (int) $c->id; ?>" <?php echo set_select('city_id', (string) $c->id); ?>><?php echo html_escape($c->name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-password">Password</label>
              <input type="password" name="password" id="nb-modal-reg-password" class="form-control" required autocomplete="new-password">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nb-modal-reg-password2">Confirm password</label>
              <input type="password" name="password2" id="nb-modal-reg-password2" class="form-control" required autocomplete="new-password">
            </div>
            <div class="col-12">
              <div class="form-check">
                <input type="checkbox" name="terms" value="1" class="form-check-input" id="nb-modal-terms" required>
                <label class="form-check-label" for="nb-modal-terms">I agree to the terms</label>
              </div>
            </div>
            <div class="col-md-6 nb-agent-only">
              <label class="form-label" for="nb-modal-reg-aadhar">Aadhar number</label>
              <input type="text" name="aadhar_no" id="nb-modal-reg-aadhar" class="form-control" value="<?php echo set_value('aadhar_no'); ?>" placeholder="12 digit Aadhar number">
            </div>
            <div class="col-md-6 nb-agent-only">
              <label class="form-label" for="nb-modal-reg-experience">Experience (years)</label>
              <input type="number" min="0" max="60" step="1" name="experience_years" id="nb-modal-reg-experience" class="form-control" value="<?php echo set_value('experience_years'); ?>" placeholder="e.g. 5">
            </div>
            <div class="col-12 nb-agent-only">
              <label class="form-label" for="nb-modal-reg-aadhar-file">Aadhar upload</label>
              <input type="file" name="aadhar_file" id="nb-modal-reg-aadhar-file" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.webp">
              <small class="text-muted">Accepted: JPG, PNG, WEBP, PDF (max 5MB).</small>
            </div>
          </div>
          <button type="submit" class="btn btn-danger w-100 mt-3">Register</button>
        <?php echo form_close(); ?>
        <p class="mt-3 mb-0 small text-center text-secondary">
          Already have an account?
          <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#nbModalLogin">Login</button>
        </p>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var userTypeHidden = document.getElementById('nb-modal-reg-user-type');
  var typeCustomer = document.getElementById('nb-type-customer');
  var typeAgent = document.getElementById('nb-type-agent');
  if (!userTypeHidden || !typeCustomer || !typeAgent) return;
  var rows = document.querySelectorAll('.nb-agent-only');
  var aadhar = document.getElementById('nb-modal-reg-aadhar');
  var exp = document.getElementById('nb-modal-reg-experience');
  var aadharFile = document.getElementById('nb-modal-reg-aadhar-file');
  var regForm = document.getElementById('nb-register-form');
  var regAlert = document.getElementById('nb-register-alert');
  function setType(type) {
    var isAgent = type === 'agent';
    typeAgent.checked = isAgent;
    typeCustomer.checked = !isAgent;
    userTypeHidden.value = isAgent ? 'agent' : 'customer';
  }
  function syncAgentFields() {
    var isAgent = userTypeHidden.value === 'agent';
    rows.forEach(function (el) { el.style.display = isAgent ? '' : 'none'; });
    if (aadhar) aadhar.required = isAgent;
    if (exp) exp.required = isAgent;
    if (aadharFile) aadharFile.required = isAgent;
  }
  typeCustomer.addEventListener('change', function () { setType('customer'); syncAgentFields(); });
  typeAgent.addEventListener('change', function () { setType('agent'); syncAgentFields(); });
  setType(userTypeHidden.value === 'agent' ? 'agent' : 'customer');
  syncAgentFields();

  function showAlert(message, ok) {
    if (!regAlert) return;
    regAlert.classList.remove('d-none', 'alert-danger', 'alert-success');
    regAlert.classList.add(ok ? 'alert-success' : 'alert-danger');
    regAlert.textContent = message;
  }

  function showLoginAlert(message, ok) {
    var loginAlert = document.getElementById('nb-login-alert');
    if (!loginAlert) return;
    loginAlert.classList.remove('d-none', 'alert-danger', 'alert-success');
    loginAlert.classList.add(ok ? 'alert-success' : 'alert-danger');
    loginAlert.textContent = message;
  }

  var loginForm = document.getElementById('nb-login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      showLoginAlert('', false);
      var loginAlert = document.getElementById('nb-login-alert');
      if (loginAlert) loginAlert.classList.add('d-none');
      
      var submitBtn = loginForm.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      fetch(loginForm.getAttribute('action'), {
        method: 'POST',
        body: new FormData(loginForm),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .then(function (r) {
          return r.json().catch(function () {
            return { success: false, message: 'Unexpected server response.' };
          });
        })
        .then(function (res) {
          if (!res || !res.success) {
            showLoginAlert((res && res.message) ? res.message : 'Login failed.', false);
            return;
          }
          showLoginAlert((res && res.message) ? res.message : 'Login successful.', true);
          setTimeout(function () {
            window.location.href = (res && res.redirect) ? res.redirect : '<?php echo site_url(''); ?>';
          }, 500);
        })
        .catch(function () {
          showLoginAlert('Network error. Please try again.', false);
        })
        .finally(function () {
          if (submitBtn) submitBtn.disabled = false;
        });
    });
  }

  if (regForm) {
    regForm.addEventListener('submit', function (e) {
      e.preventDefault();
      if (regAlert) {
        regAlert.classList.add('d-none');
        regAlert.textContent = '';
      }
      var submitBtn = regForm.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
      }
      fetch(regForm.getAttribute('action'), {
        method: 'POST',
        body: new FormData(regForm),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
        .then(function (r) {
          return r.json().catch(function () {
            return { success: false, message: 'Unexpected server response.' };
          });
        })
        .then(function (res) {
          if (!res || !res.success) {
            showAlert((res && res.message) ? res.message : 'Registration failed.', false);
            return;
          }
          showAlert((res && res.message) ? res.message : 'Registration successful.', true);
          setTimeout(function () {
            window.location.href = (res && res.redirect) ? res.redirect : '<?php echo site_url('tenant/dashboard'); ?>';
          }, 700);
        })
        .catch(function () {
          showAlert('Network error. Please try again.', false);
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
          }
        });
    });
  }
})();
</script>
