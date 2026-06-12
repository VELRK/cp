/**
 * Infinite scroll for /search — loads pages via GET search/more?page=N (same filters as current results).
 */
(function () {
  var cfgEl = document.getElementById('nb-search-infinite-config');
  if (!cfgEl) return;

  var cfg;
  try {
    cfg = JSON.parse(cfgEl.textContent || '{}');
  } catch (e) {
    return;
  }

  var grid = document.getElementById('nb-search-property-grid');
  var sentinel = document.getElementById('nb-search-infinite-sentinel');
  var spinner = document.getElementById('nb-search-infinite-spinner');
  var endEl = document.getElementById('nb-search-infinite-end');
  var errEl = document.getElementById('nb-search-infinite-err');
  var showingToEl = document.getElementById('nb-search-showing-to');
  if (!grid || !sentinel || !cfg.moreUrl) return;

  var loading = false;
  var nextPage = typeof cfg.nextPage === 'number' ? cfg.nextPage : 2;
  var hasMore = cfg.hasMore !== false;
  var paramsBase = cfg.params && typeof cfg.params === 'object' ? cfg.params : {};

  function showSpinner(on) {
    if (spinner) spinner.style.display = on ? 'inline-block' : 'none';
  }

  function buildQuery() {
    var p = new URLSearchParams();
    Object.keys(paramsBase).forEach(function (k) {
      var v = paramsBase[k];
      if (v === null || v === undefined || v === '') return;
      p.set(k, String(v));
    });
    p.set('page', String(nextPage));
    return p.toString();
  }

  function loadMore() {
    if (!hasMore || loading) return;
    loading = true;
    showSpinner(true);
    if (errEl) errEl.classList.add('d-none');

    fetch(cfg.moreUrl + '?' + buildQuery(), {
      credentials: 'same-origin',
      headers: { Accept: 'application/json' }
    })
      .then(function (r) {
        return r.text().then(function (t) {
          var data = {};
          try {
            data = t ? JSON.parse(t) : {};
          } catch (e) {
            throw new Error('bad json');
          }
          return data;
        });
      })
      .then(function (data) {
        if (!data || data.ok !== true) throw new Error('fail');
        if (data.html) {
          grid.insertAdjacentHTML('beforeend', data.html);
        }
        if (showingToEl && typeof data.showing_to === 'number') {
          showingToEl.textContent = String(data.showing_to);
        }
        hasMore = !!data.has_more;
        if (typeof data.next_page === 'number') {
          nextPage = data.next_page;
        } else if (hasMore) {
          nextPage += 1;
        }
        if (!hasMore) {
          sentinel.classList.add('nb-search-infinite-done');
          if (endEl) endEl.classList.remove('d-none');
          io.disconnect();
        }
      })
      .catch(function () {
        if (errEl) errEl.classList.remove('d-none');
      })
      .finally(function () {
        loading = false;
        showSpinner(false);
      });
  }

  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) loadMore();
      });
    },
    { root: null, rootMargin: '280px', threshold: 0 }
  );

  io.observe(sentinel);
})();
