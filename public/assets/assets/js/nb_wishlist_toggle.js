(function () {
  'use strict';

  function readJson(response) {
    return response.text().then(function (text) {
      var payload = {};
      try { payload = text ? JSON.parse(text) : {}; } catch (e) {}
      return { ok: response.ok, status: response.status, payload: payload };
    });
  }

  function setBtnState(btn, wishlisted) {
    var active = !!wishlisted;
    btn.dataset.wishlisted = active ? '1' : '0';
    btn.classList.toggle('is-active', active);
    btn.setAttribute('aria-label', active ? 'Remove from wishlist' : 'Add to wishlist');
    btn.setAttribute('title', active ? 'Remove from wishlist' : 'Add to wishlist');
    var icon = btn.querySelector('i.bi');
    if (icon) {
      icon.classList.remove('bi-heart', 'bi-heart-fill');
      icon.classList.add(active ? 'bi-heart-fill' : 'bi-heart');
    }
  }

  function showMsg(msg) {
    if (typeof window.bootstrap !== 'undefined') {
      var el = document.getElementById('nbModalLogin');
      if (el) {
        window.bootstrap.Modal.getOrCreateInstance(el).show();
        return;
      }
    }
    window.alert(msg || 'Please login first.');
  }

  function checkButton(btn) {
    var userId = btn.dataset.userId || '';
    var propertyId = btn.dataset.propertyId || '';
    if (!userId || !propertyId) {
      setBtnState(btn, false);
      return;
    }
    var qs = new URLSearchParams({ user_id: userId, property_id: propertyId });
    fetch((window.NB_BASE_URL || '/') + 'api/mobile/wishlist/check?' + qs.toString(), {
      method: 'GET',
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    })
      .then(readJson)
      .then(function (res) {
        var d = (res.payload && res.payload.data) || {};
        setBtnState(btn, !!d.wishlisted);
      })
      .catch(function () {});
  }

  function toggleButton(btn) {
    var userId = btn.dataset.userId || '';
    var propertyId = btn.dataset.propertyId || '';
    if (!userId) {
      showMsg('Please login to use wishlist.');
      return;
    }
    if (!propertyId) {
      return;
    }
    if (btn.dataset.loading === '1') {
      return;
    }
    btn.dataset.loading = '1';
    btn.disabled = true;

    var body = new URLSearchParams({ user_id: userId, property_id: propertyId });
    fetch((window.NB_BASE_URL || '/') + 'api/mobile/wishlist/store', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json' },
      body: body.toString()
    })
      .then(readJson)
      .then(function (res) {
        var d = (res.payload && res.payload.data) || {};
        if (!res.ok || res.payload.success !== true || typeof d.wishlisted === 'undefined') {
          throw new Error((res.payload && res.payload.message) || 'Wishlist request failed');
        }
        setBtnState(btn, !!d.wishlisted);
      })
      .catch(function (e) {
        window.alert(e && e.message ? e.message : 'Could not update wishlist.');
      })
      .finally(function () {
        btn.dataset.loading = '0';
        btn.disabled = false;
      });
  }

  function initAll(root) {
    var scope = root || document;
    var buttons = scope.querySelectorAll('.nb-wishlist-toggle');
    buttons.forEach(function (btn) {
      if (btn.dataset.bound === '1') return;
      btn.dataset.bound = '1';
      setBtnState(btn, btn.dataset.wishlisted === '1');
      checkButton(btn);
      btn.addEventListener('click', function () { toggleButton(btn); });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initAll(document);
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (m) {
        m.addedNodes.forEach(function (n) {
          if (n && n.nodeType === 1) {
            initAll(n);
          }
        });
      });
    });
    observer.observe(document.body, { childList: true, subtree: true });
  });
})();

