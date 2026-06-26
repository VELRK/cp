<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$base = html_escape($base_api_url);
$embed_admin = !empty($embed_admin);
?><?php if (!$embed_admin): ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo html_escape($page_title); ?> | Coimbatore Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php else: ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?php endif; ?>
  <style>
    :root {
      --dev-bg: #0f172a;
      --dev-panel: #1e293b;
      --dev-border: #334155;
      --dev-text: #e2e8f0;
      --dev-muted: #94a3b8;
      --dev-accent: #f2b203;
      --dev-get: #22c55e;
      --dev-post: #3b82f6;
      --dev-put: #f59e0b;
      --dev-del: #ef4444;
    }
    body { background: var(--dev-bg); color: var(--dev-text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }
    .dev-embed-wrap { background: var(--dev-bg); border-radius: 12px; overflow: hidden; margin: -0.25rem 0 0; }
    .dev-embed-wrap .dev-layout { min-height: calc(100vh - 200px); }
    .dev-embed-wrap .dev-sidebar { max-height: calc(100vh - 200px); }
    .dev-sidebar-brand { background: rgba(0,0,0,.15); }
    .dev-topbar { background: var(--dev-panel); border-bottom: 1px solid var(--dev-border); padding: .75rem 1.25rem; }
    .dev-layout { display: flex; min-height: calc(100vh - 56px); }
    .dev-sidebar { width: 300px; flex-shrink: 0; background: var(--dev-panel); border-right: 1px solid var(--dev-border); overflow-y: auto; max-height: calc(100vh - 56px); display: flex; flex-direction: column; }
    .dev-sidebar-list { flex: 1; overflow-y: auto; }
    .dev-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
    .dev-group-title { font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; color: var(--dev-muted); padding: .75rem 1rem .35rem; font-weight: 700; }
    .dev-endpoint { display: block; padding: .45rem 1rem; color: var(--dev-text); text-decoration: none; font-size: .82rem; border-left: 3px solid transparent; cursor: pointer; }
    .dev-endpoint:hover { background: rgba(255,255,255,.05); color: #fff; }
    .dev-endpoint.active { background: rgba(242,178,3,.12); border-left-color: var(--dev-accent); color: #fff; }
    .dev-endpoint .method { font-size: .65rem; font-weight: 700; padding: .1rem .35rem; border-radius: 3px; margin-right: .4rem; }
    .m-GET { background: rgba(34,197,94,.2); color: var(--dev-get); }
    .m-POST { background: rgba(59,130,246,.2); color: var(--dev-post); }
    .m-PUT { background: rgba(245,158,11,.2); color: var(--dev-put); }
    .m-DELETE { background: rgba(239,68,68,.2); color: var(--dev-del); }
    .dev-panel { background: var(--dev-panel); border: 1px solid var(--dev-border); border-radius: 8px; margin: 1rem; }
    .dev-panel-head { padding: .75rem 1rem; border-bottom: 1px solid var(--dev-border); font-weight: 600; font-size: .9rem; }
    .dev-panel-body { padding: 1rem; }
    .dev-url-bar { display: flex; gap: .5rem; align-items: stretch; }
    .dev-url-bar .method-badge { min-width: 72px; text-align: center; font-weight: 700; border-radius: 6px; border: none; color: #fff; font-size: .85rem; }
    .dev-url-bar input { flex: 1; background: #0f172a; border: 1px solid var(--dev-border); color: var(--dev-text); border-radius: 6px; padding: .5rem .75rem; font-family: monospace; font-size: .85rem; }
    .dev-url-bar .btn-send { background: var(--dev-accent); color: #0b2c56; font-weight: 700; border: none; border-radius: 6px; padding: 0 1.25rem; }
    .dev-url-bar .btn-send:hover { filter: brightness(1.08); }
    .dev-tabs .nav-link { color: var(--dev-muted); font-size: .85rem; border: none; border-radius: 0; padding: .6rem 1rem; }
    .dev-tabs .nav-link.active { color: var(--dev-accent); background: transparent; border-bottom: 2px solid var(--dev-accent); }
    .dev-field-row { display: flex; gap: .5rem; margin-bottom: .5rem; }
    .dev-field-row input { flex: 1; background: #0f172a; border: 1px solid var(--dev-border); color: var(--dev-text); border-radius: 4px; padding: .35rem .5rem; font-size: .82rem; }
    .dev-textarea { width: 100%; min-height: 140px; background: #0f172a; border: 1px solid var(--dev-border); color: #a5f3fc; border-radius: 6px; padding: .75rem; font-family: 'Consolas', monospace; font-size: .82rem; resize: vertical; }
    .dev-response-meta { display: flex; gap: 1rem; font-size: .82rem; color: var(--dev-muted); margin-bottom: .5rem; }
    .dev-response-meta .status-ok { color: var(--dev-get); }
    .dev-response-meta .status-err { color: var(--dev-del); }
    .dev-sample-box { background: #0f172a; border: 1px dashed var(--dev-border); border-radius: 6px; padding: .75rem; font-family: monospace; font-size: .78rem; color: #cbd5e1; max-height: 220px; overflow: auto; white-space: pre-wrap; }
    .dev-token-input { background: #0f172a; border: 1px solid var(--dev-border); color: var(--dev-text); border-radius: 6px; padding: .4rem .6rem; font-size: .82rem; width: 100%; max-width: 480px; font-family: monospace; }
    .dev-desc { color: var(--dev-muted); font-size: .85rem; margin-bottom: 1rem; }
    .badge-auth { font-size: .7rem; }
    @media (max-width: 768px) {
      .dev-layout { flex-direction: column; }
      .dev-sidebar { width: 100%; max-height: 240px; }
    }
  </style>
<?php if (!$embed_admin): ?>
</head>
<body>
  <header class="dev-topbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-collection text-warning fs-5"></i>
      <div>
        <div class="fw-bold">API Collection</div>
        <div class="small text-secondary">Test web &amp; mobile APIs — like Postman</div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <label class="small text-secondary mb-0">X-Api-Token</label>
      <input type="text" id="globalToken" class="dev-token-input" placeholder="Paste token from Login response">
      <a href="<?php echo site_url('panel/api-collection'); ?>" class="btn btn-sm btn-outline-secondary">Admin panel</a>
      <a href="<?php echo site_url(''); ?>" class="btn btn-sm btn-outline-secondary">Public site</a>
    </div>
  </header>
<?php else: ?>
<div class="dev-embed-wrap">
  <div class="dev-topbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="small text-secondary">Paste <strong>X-Api-Token</strong> from Login response, then Send.</div>
    <input type="text" id="globalToken" class="dev-token-input" placeholder="X-Api-Token">
  </div>
<?php endif; ?>

  <div class="dev-layout">
    <aside class="dev-sidebar">
      <div class="dev-sidebar-brand px-3 py-3 border-bottom border-secondary border-opacity-25">
        <div class="fw-bold text-warning"><i class="bi bi-collection me-1"></i> API Collection</div>
        <div class="small text-secondary mt-1">Web &amp; mobile endpoints</div>
      </div>
      <div class="dev-sidebar-list" id="sidebar"></div>
    </aside>

    <main class="dev-main">
      <div class="dev-panel m-3 mb-0">
        <div class="dev-panel-body">
          <div class="d-flex align-items-center gap-2 mb-2">
            <h1 class="h5 mb-0" id="epName">Select an endpoint</h1>
            <span id="epAuthBadge"></span>
          </div>
          <p class="dev-desc mb-2" id="epDesc">Choose an API from the left sidebar. Sample request &amp; response are shown below.</p>
          <div class="dev-url-bar mb-3">
            <span class="method-badge dev-url-bar method-badge" id="methodBadge">GET</span>
            <input type="text" id="urlInput" readonly value="<?php echo $base; ?>/api/nb/cities">
            <button type="button" class="btn-send" id="btnSend"><i class="bi bi-send-fill me-1"></i> Send</button>
          </div>

          <ul class="nav dev-tabs border-bottom border-secondary border-opacity-25 mb-3" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabParams" type="button">Params</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabHeaders" type="button">Headers</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBody" type="button">Body</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSampleRequest" type="button">Sample request</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSample" type="button">Sample response</button></li>
          </ul>

          <div class="tab-content">
            <div class="tab-pane fade show active" id="tabParams">
              <div id="paramsContainer"></div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="btnAddParam"><i class="bi bi-plus"></i> Add param</button>
            </div>
            <div class="tab-pane fade" id="tabHeaders">
              <div id="headersContainer"></div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="btnAddHeader"><i class="bi bi-plus"></i> Add header</button>
            </div>
            <div class="tab-pane fade" id="tabBody">
              <div class="mb-2 small text-secondary">Body type: <span id="bodyTypeLabel">none</span></div>
              <div id="bodyFormFields"></div>
              <textarea id="bodyJson" class="dev-textarea d-none" placeholder='{"key": "value"}'></textarea>
            </div>
            <div class="tab-pane fade" id="tabSampleRequest">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-secondary">Copy this into Postman or use Params/Body tabs above (pre-filled on Send).</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopySampleRequest">Copy</button>
              </div>
              <div class="dev-sample-box" id="sampleRequest">Select an endpoint to view sample request.</div>
            </div>
            <div class="tab-pane fade" id="tabSample">
              <div class="dev-sample-box" id="sampleResponse">Select an endpoint to view sample JSON response.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="dev-panel flex-grow-1 mx-3 mb-3">
        <div class="dev-panel-head d-flex justify-content-between align-items-center">
          <span><i class="bi bi-terminal me-1"></i> Response</span>
          <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopyResponse">Copy</button>
        </div>
        <div class="dev-panel-body">
          <div class="dev-response-meta" id="responseMeta">
            <span>Status: <strong id="respStatus">—</strong></span>
            <span>Time: <strong id="respTime">—</strong></span>
          </div>
          <pre class="dev-sample-box mb-0" id="responseBody" style="max-height:400px">Click Send to call the live API.</pre>
        </div>
      </div>
    </main>
  </div>
<?php if ($embed_admin): ?></div><?php endif; ?>

  <script>
    window.API_CATALOG = <?php echo $catalog_json; ?>;
    window.API_BASE = <?php echo json_encode($base_api_url); ?>;
  </script>
  <script>
  (function () {
    var catalog = window.API_CATALOG || { groups: [] };
    var baseUrl = window.API_BASE || '';
    var current = null;
    var sidebar = document.getElementById('sidebar');

    function methodClass(m) { return 'm-' + (m || 'GET').toUpperCase(); }

    function buildSidebar() {
      var html = '';
      (catalog.groups || []).forEach(function (g) {
        html += '<div class="dev-group-title">' + esc(g.title) + '</div>';
        (g.endpoints || []).forEach(function (ep) {
          html += '<a class="dev-endpoint" data-id="' + esc(ep.id) + '" href="#">' +
            '<span class="method ' + methodClass(ep.method) + '">' + esc(ep.method) + '</span>' +
            esc(ep.name) + '</a>';
        });
      });
      sidebar.innerHTML = html;
      sidebar.querySelectorAll('.dev-endpoint').forEach(function (el) {
        el.addEventListener('click', function (e) {
          e.preventDefault();
          selectEndpoint(el.getAttribute('data-id'));
        });
      });
    }

    function findEndpoint(id) {
      for (var i = 0; i < (catalog.groups || []).length; i++) {
        var eps = catalog.groups[i].endpoints || [];
        for (var j = 0; j < eps.length; j++) {
          if (eps[j].id === id) return eps[j];
        }
      }
      return null;
    }

    function esc(s) {
      if (s == null) return '';
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;');
    }

    function kvDisplay(val) {
      if (val === true || val === false) return String(val);
      if (val === 0) return '0';
      return val == null ? '' : String(val);
    }

    function buildSampleRequest(ep) {
      var path = (ep.path || '').replace(/^\//, '');
      var url = baseUrl + '/' + path;
      var query = ep.query || {};
      var qs = new URLSearchParams();
      Object.keys(query).forEach(function (k) {
        if (k && query[k] !== undefined && query[k] !== null && String(query[k]) !== '') {
          qs.set(k, String(query[k]));
        }
      });
      var qsStr = qs.toString();
      if (qsStr) url += (url.indexOf('?') >= 0 ? '&' : '?') + qsStr;

      var headers = { Accept: 'application/json' };
      if (ep.auth) {
        headers['X-Api-Token'] = '<paste token from Login response>';
      }
      if (ep.body_type === 'json') {
        headers['Content-Type'] = 'application/json';
      } else if (ep.body_type === 'form') {
        headers['Content-Type'] = 'multipart/form-data';
      }

      var sample = {
        method: ep.method || 'GET',
        url: url,
        headers: headers,
      };
      if (Object.keys(query).length) {
        sample.query = query;
      }
      if (ep.body && Object.keys(ep.body).length) {
        sample.body = ep.body;
      }
      if (ep.sample_request && typeof ep.sample_request === 'object') {
        Object.keys(ep.sample_request).forEach(function (k) {
          sample[k] = ep.sample_request[k];
        });
      }
      if (ep.auth) {
        sample.note = 'Requires X-Api-Token header (run Verify OTP or Login first).';
      }
      return sample;
    }

    function selectEndpoint(id) {
      var ep = findEndpoint(id);
      if (!ep) return;
      current = ep;
      sidebar.querySelectorAll('.dev-endpoint').forEach(function (el) {
        el.classList.toggle('active', el.getAttribute('data-id') === id);
      });

      document.getElementById('epName').textContent = ep.name;
      document.getElementById('epDesc').textContent = ep.description || '';
      document.getElementById('epAuthBadge').innerHTML = ep.auth
        ? '<span class="badge bg-warning text-dark badge-auth">Auth required</span>'
        : '<span class="badge bg-secondary badge-auth">Public</span>';

      var badge = document.getElementById('methodBadge');
      badge.textContent = ep.method;
      badge.className = 'method-badge dev-url-bar ' + methodClass(ep.method);

      document.getElementById('urlInput').value = baseUrl + '/' + (ep.path || '').replace(/^\//, '');

      fillKeyValue('paramsContainer', ep.query || {});
      fillKeyValue('headersContainer', { 'Content-Type': ep.body_type === 'json' ? 'application/json' : (ep.body_type === 'form' ? 'multipart/form-data' : 'application/json') });

      document.getElementById('bodyTypeLabel').textContent = ep.body_type || 'none';
      var bodyJson = document.getElementById('bodyJson');
      var formFields = document.getElementById('bodyFormFields');
      formFields.innerHTML = '';
      bodyJson.classList.add('d-none');
      formFields.classList.add('d-none');

      if (ep.body_type === 'json') {
        bodyJson.classList.remove('d-none');
        bodyJson.value = JSON.stringify(ep.body || {}, null, 2);
      } else if (ep.body_type === 'form') {
        formFields.classList.remove('d-none');
        fillKeyValue('bodyFormFields', ep.body || {}, true);
      }

      document.getElementById('sampleRequest').textContent = JSON.stringify(buildSampleRequest(ep), null, 2);
      document.getElementById('sampleResponse').textContent = JSON.stringify(ep.sample_response || {}, null, 2);
      document.getElementById('responseBody').textContent = 'Click Send to call the live API.';
      document.getElementById('respStatus').textContent = '—';
      document.getElementById('respTime').textContent = '—';
    }

    function fillKeyValue(containerId, obj, isBody) {
      var c = document.getElementById(containerId);
      c.innerHTML = '';
      var keys = Object.keys(obj || {});
      if (!keys.length) keys = [''];
      keys.forEach(function (k) {
        c.appendChild(makeKvRow(k, obj[k] || '', isBody));
      });
    }

    function makeKvRow(key, val, isBody) {
      var row = document.createElement('div');
      row.className = 'dev-field-row';
      row.innerHTML =
        '<input type="text" class="kv-key" placeholder="key" value="' + esc(key) + '">' +
        '<input type="text" class="kv-val" placeholder="value" value="' + esc(kvDisplay(val)) + '">' +
        '<button type="button" class="btn btn-sm btn-outline-danger kv-remove"><i class="bi bi-x"></i></button>';
      row.querySelector('.kv-remove').addEventListener('click', function () { row.remove(); });
      return row;
    }

    function readKv(containerId) {
      var out = {};
      document.querySelectorAll('#' + containerId + ' .dev-field-row').forEach(function (row) {
        var k = row.querySelector('.kv-key').value.trim();
        var v = row.querySelector('.kv-val').value.trim();
        if (k) out[k] = v;
      });
      return out;
    }

    document.getElementById('btnAddParam').addEventListener('click', function () {
      document.getElementById('paramsContainer').appendChild(makeKvRow('', ''));
    });
    document.getElementById('btnAddHeader').addEventListener('click', function () {
      document.getElementById('headersContainer').appendChild(makeKvRow('', ''));
    });

    document.getElementById('btnSend').addEventListener('click', sendRequest);

    async function sendRequest() {
      if (!current) { alert('Select an endpoint first'); return; }

      var url = document.getElementById('urlInput').value.trim();
      var params = readKv('paramsContainer');
      var qs = new URLSearchParams(params).toString();
      if (qs) url += (url.indexOf('?') >= 0 ? '&' : '?') + qs;

      var headers = readKv('headersContainer');
      var token = document.getElementById('globalToken').value.trim();
      if (token) headers['X-Api-Token'] = token;

      var opts = { method: current.method || 'GET', headers: {} };
      Object.keys(headers).forEach(function (k) {
        if (k.toLowerCase() !== 'content-type' || current.body_type !== 'form') {
          opts.headers[k] = headers[k];
        }
      });

      if (current.body_type === 'json' && ['POST','PUT','PATCH'].indexOf(current.method) >= 0) {
        opts.headers['Content-Type'] = 'application/json';
        try {
          opts.body = document.getElementById('bodyJson').value.trim() || '{}';
          JSON.parse(opts.body);
        } catch (e) {
          alert('Invalid JSON body');
          return;
        }
      } else if (current.body_type === 'form' && ['POST','PUT','PATCH'].indexOf(current.method) >= 0) {
        var fd = new FormData();
        var formObj = readKv('bodyFormFields');
        Object.keys(formObj).forEach(function (k) { fd.append(k, formObj[k]); });
        opts.body = fd;
      }

      var t0 = performance.now();
      document.getElementById('responseBody').textContent = 'Loading...';
      try {
        var res = await fetch(url, opts);
        var elapsed = Math.round(performance.now() - t0);
        var text = await res.text();
        var display = text;
        try { display = JSON.stringify(JSON.parse(text), null, 2); } catch (e) {}
        document.getElementById('respStatus').textContent = res.status + ' ' + res.statusText;
        document.getElementById('respStatus').className = res.ok ? 'status-ok' : 'status-err';
        document.getElementById('respTime').textContent = elapsed + ' ms';
        document.getElementById('responseBody').textContent = display;
        try {
          var parsed = JSON.parse(text);
          if (parsed && parsed.success && parsed.token) {
            document.getElementById('globalToken').value = parsed.token;
          }
        } catch (e) {}
      } catch (err) {
        document.getElementById('respStatus').textContent = 'Error';
        document.getElementById('respStatus').className = 'status-err';
        document.getElementById('respTime').textContent = '—';
        document.getElementById('responseBody').textContent = String(err);
      }
    }

    document.getElementById('btnCopyResponse').addEventListener('click', function () {
      var t = document.getElementById('responseBody').textContent;
      navigator.clipboard.writeText(t).then(function () { alert('Copied'); });
    });

    document.getElementById('btnCopySampleRequest').addEventListener('click', function () {
      var t = document.getElementById('sampleRequest').textContent;
      navigator.clipboard.writeText(t).then(function () { alert('Copied sample request'); });
    });

    buildSidebar();
    var first = catalog.groups && catalog.groups[0] && catalog.groups[0].endpoints && catalog.groups[0].endpoints[0];
    if (first) selectEndpoint(first.id);
  })();
  </script>
<?php if (!$embed_admin): ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>
