<?php defined('BASEPATH') OR exit('No direct script access allowed');
$modal_cities = isset($modal_cities) && is_array($modal_cities) ? $modal_cities : array();
?>
<div class="modal fade" id="nbModalLogin" tabindex="-1" aria-labelledby="nbModalLoginLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header border-0 pb-0">
        <div>
          <h2 class="modal-title h5 fw-bold text-primary mb-0" id="nbModalLoginLabel">Sign in with Phone</h2>
          <p class="text-muted small mb-0 mt-1" id="nb-login-subtitle">We will send a 4-digit OTP to your WhatsApp</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body pt-2 pb-4">
        <div id="nb-login-alert" class="alert d-none" role="alert"></div>

        <div id="nb-login-step-phone">
          <div class="mb-3">
            <label class="form-label small fw-semibold" for="nb-modal-login-phone">Mobile Number</label>
            <div class="input-group">
              <span class="input-group-text bg-light fw-semibold small">+91</span>
              <input type="tel" id="nb-modal-login-phone" class="form-control" inputmode="numeric" maxlength="10" placeholder="10-digit mobile number" autocomplete="tel-national">
            </div>
            <div class="text-muted mt-2" style="font-size:0.75rem;">OTP will be delivered on WhatsApp</div>
          </div>
          <button type="button" class="btn btn-danger w-100 rounded-pill fw-semibold" id="nb-login-send-otp">Send OTP</button>
        </div>

        <div id="nb-login-step-otp" class="d-none">
          <button type="button" class="btn btn-link btn-sm p-0 mb-3 text-decoration-none" id="nb-login-change-phone">&larr; Change number</button>
          <div class="mb-3">
            <label class="form-label small fw-semibold" for="nb-modal-login-otp">Enter 4-digit OTP</label>
            <input type="text" id="nb-modal-login-otp" class="form-control text-center fw-bold" inputmode="numeric" maxlength="4" placeholder="• • • •" autocomplete="one-time-code" style="letter-spacing:0.35em;font-size:1.25rem;">
          </div>
          <button type="button" class="btn btn-danger w-100 rounded-pill fw-semibold mb-3" id="nb-login-verify-otp">Verify &amp; Sign In</button>
          <div class="text-center small">
            <button type="button" class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none d-none" id="nb-login-resend-otp">Resend OTP</button>
            <span class="text-muted d-none" id="nb-login-resend-timer">Resend OTP in 60s</span>
          </div>
        </div>

        <p class="mt-3 mb-0 small text-center text-secondary">
          Don&apos;t have an account?
          <button type="button" class="btn btn-link btn-sm p-0 align-baseline" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#nbModalRegister">Register</button>
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

  (function initNbOtpLogin() {
    var sendOtpUrl = '<?php echo site_url('api/nb/send-otp'); ?>';
    var verifyOtpUrl = '<?php echo site_url('api/nb/verify-otp'); ?>';
    var resendOtpUrl = '<?php echo site_url('api/nb/resend-otp'); ?>';
    var phoneInput = document.getElementById('nb-modal-login-phone');
    var otpInput = document.getElementById('nb-modal-login-otp');
    var stepPhone = document.getElementById('nb-login-step-phone');
    var stepOtp = document.getElementById('nb-login-step-otp');
    var sendBtn = document.getElementById('nb-login-send-otp');
    var verifyBtn = document.getElementById('nb-login-verify-otp');
    var changePhoneBtn = document.getElementById('nb-login-change-phone');
    var resendBtn = document.getElementById('nb-login-resend-otp');
    var resendTimerEl = document.getElementById('nb-login-resend-timer');
    var subtitle = document.getElementById('nb-login-subtitle');
    var modalTitle = document.getElementById('nbModalLoginLabel');
    var loginModal = document.getElementById('nbModalLogin');
    var currentPhone = '';
    var resendTimer = null;
    var resendSeconds = 0;

    if (!phoneInput || !sendBtn) return;

    function normalizePhone(val) {
      return String(val || '').replace(/\D/g, '').slice(-10);
    }

    function showStep(step) {
      if (stepPhone) stepPhone.classList.toggle('d-none', step !== 'phone');
      if (stepOtp) stepOtp.classList.toggle('d-none', step !== 'otp');
      if (modalTitle) modalTitle.textContent = step === 'phone' ? 'Sign in with Phone' : 'Verify OTP';
      if (subtitle) {
        subtitle.textContent = step === 'phone'
          ? 'We will send a 4-digit OTP to your WhatsApp'
          : ('OTP sent to +91 ' + currentPhone);
      }
    }

    function resetLoginModal() {
      currentPhone = '';
      if (phoneInput) phoneInput.value = '';
      if (otpInput) otpInput.value = '';
      showStep('phone');
      clearInterval(resendTimer);
      resendSeconds = 0;
      if (resendBtn) resendBtn.classList.add('d-none');
      if (resendTimerEl) resendTimerEl.classList.add('d-none');
      var loginAlert = document.getElementById('nb-login-alert');
      if (loginAlert) loginAlert.classList.add('d-none');
    }

    function startResendTimer() {
      resendSeconds = 60;
      if (resendBtn) resendBtn.classList.add('d-none');
      if (resendTimerEl) {
        resendTimerEl.classList.remove('d-none');
        resendTimerEl.textContent = 'Resend OTP in ' + resendSeconds + 's';
      }
      clearInterval(resendTimer);
      resendTimer = setInterval(function () {
        resendSeconds -= 1;
        if (resendSeconds <= 0) {
          clearInterval(resendTimer);
          if (resendTimerEl) resendTimerEl.classList.add('d-none');
          if (resendBtn) resendBtn.classList.remove('d-none');
          return;
        }
        if (resendTimerEl) resendTimerEl.textContent = 'Resend OTP in ' + resendSeconds + 's';
      }, 1000);
    }

    function postJson(url, payload) {
      return fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function (r) {
        return r.json().catch(function () {
          return { success: false, message: 'Unexpected server response.' };
        });
      });
    }

    function dashboardUrlForRole(role) {
      if (role === 'owner') {
        return '<?php echo site_url('owner/dashboard'); ?>';
      }
      if (role === 'tenant' || role === 'customer') {
        return '<?php echo site_url('tenant/dashboard'); ?>';
      }
      return '<?php echo site_url(''); ?>';
    }

    function completeLogin(res) {
      if (res.token) {
        try { localStorage.setItem('nb_token', res.token); } catch (e) {}
      }
      showLoginAlert((res && res.message) ? res.message : 'Signed in successfully.', true);
      var role = (res && res.user && res.user.role) ? res.user.role : 'tenant';
      setTimeout(function () {
        window.location.href = dashboardUrlForRole(role);
      }, 500);
    }

    phoneInput.addEventListener('input', function () {
      phoneInput.value = normalizePhone(phoneInput.value);
    });

    if (modalTitle && loginModal) {
      loginModal.addEventListener('hidden.bs.modal', resetLoginModal);
      loginModal.addEventListener('show.bs.modal', resetLoginModal);
    }

    sendBtn.addEventListener('click', function () {
      var phone = normalizePhone(phoneInput.value);
      if (phone.length !== 10) {
        showLoginAlert('Enter a valid 10-digit mobile number.', false);
        return;
      }
      sendBtn.disabled = true;
      postJson(sendOtpUrl, { phone: phone, country_code: '+91' })
        .then(function (res) {
          if (!res || !res.success) {
            showLoginAlert((res && res.message) ? res.message : 'Could not send OTP.', false);
            return;
          }
          currentPhone = phone;
          showStep('otp');
          if (otpInput) {
            otpInput.value = '';
            otpInput.focus();
          }
          startResendTimer();
          var msg = 'OTP sent to your WhatsApp number.';
          if (res.development_mode && res.otp) msg = 'OTP sent (dev mode): ' + res.otp;
          showLoginAlert(msg, true);
        })
        .catch(function () {
          showLoginAlert('Network error. Please try again.', false);
        })
        .finally(function () {
          sendBtn.disabled = false;
        });
    });

    if (changePhoneBtn) {
      changePhoneBtn.addEventListener('click', function () {
        showStep('phone');
        if (otpInput) otpInput.value = '';
      });
    }

    if (verifyBtn) {
      verifyBtn.addEventListener('click', function () {
        var otp = String(otpInput ? otpInput.value : '').replace(/\D/g, '').slice(0, 4);
        if (otp.length !== 4) {
          showLoginAlert('Enter the 4-digit OTP.', false);
          return;
        }
        verifyBtn.disabled = true;
        postJson(verifyOtpUrl, { phone: currentPhone, otp: otp, country_code: '+91' })
          .then(function (res) {
            if (!res || !res.success) {
              showLoginAlert((res && res.message) ? res.message : 'Invalid OTP.', false);
              return;
            }
            completeLogin(res);
          })
          .catch(function () {
            showLoginAlert('Network error. Please try again.', false);
          })
          .finally(function () {
            verifyBtn.disabled = false;
          });
      });
    }

    if (resendBtn) {
      resendBtn.addEventListener('click', function () {
        if (resendSeconds > 0 || !currentPhone) return;
        resendBtn.disabled = true;
        postJson(resendOtpUrl, { phone: currentPhone, country_code: '+91' })
          .then(function (res) {
            if (!res || !res.success) {
              showLoginAlert((res && res.message) ? res.message : 'Could not resend OTP.', false);
              return;
            }
            startResendTimer();
            var msg = 'A new OTP has been sent to your WhatsApp.';
            if (res.development_mode && res.otp) msg = 'OTP resent (dev mode): ' + res.otp;
            showLoginAlert(msg, true);
          })
          .catch(function () {
            showLoginAlert('Network error. Please try again.', false);
          })
          .finally(function () {
            resendBtn.disabled = false;
          });
      });
    }

    if (otpInput) {
      otpInput.addEventListener('input', function () {
        otpInput.value = String(otpInput.value || '').replace(/\D/g, '').slice(0, 4);
      });
    }
  })();

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
