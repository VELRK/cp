/**
 * Property form: cover image selection across existing + new file previews.
 */
(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }
  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function existingCount() {
    return qsa('.nb-prop-existing-thumb:not(.d-none)').length;
  }

  function recalcCoverMax() {
    var cover = qs('#nbCoverIndex');
    var fileInput = qs('#nbPropImages');
    var nExisting = existingCount();
    var nNew = fileInput && fileInput.files ? fileInput.files.length : 0;
    var total = nExisting + nNew;
    if (cover) {
      if (total < 1) {
        cover.value = '0';
      } else if (parseInt(cover.value, 10) >= total) {
        cover.value = '0';
      }
    }
    highlightCover();
  }

  function highlightCover() {
    var cover = qs('#nbCoverIndex');
    var idx = cover ? parseInt(cover.value, 10) || 0 : 0;
    qsa('.nb-cover-choice').forEach(function (el) {
      var i = parseInt(el.dataset.coverIdx, 10);
      var on = i === idx;
      el.classList.toggle('border', on);
      el.classList.toggle('border-3', on);
      el.classList.toggle('border-danger', on);
      el.classList.toggle('shadow', on);
    });
  }

  window.nbSetCover = function (idx) {
    var cover = qs('#nbCoverIndex');
    if (cover) {
      cover.value = String(idx);
    }
    highlightCover();
  };

  function buildNewPreviews() {
    var wrap = qs('#nbPropNewPreview');
    var fileInput = qs('#nbPropImages');
    if (!wrap || !fileInput) {
      return;
    }
    wrap.innerHTML = '';
    var files = fileInput.files;
    var base = existingCount();
    if (!files || !files.length) {
      recalcCoverMax();
      return;
    }
    for (var i = 0; i < files.length; i++) {
      (function (j) {
        var idx = base + j;
        var col = document.createElement('div');
        col.className = 'col-6 col-md-4 nb-cover-choice mb-2';
        col.dataset.coverIdx = String(idx);
        col.innerHTML =
          '<button type="button" class="btn p-0 border rounded-3 overflow-hidden w-100 nb-cover-btn" data-cover-idx="' +
          idx +
          '">' +
          '<span class="d-block bg-light" style="height:100px"><span class="small text-muted p-2">Loading…</span></span></button>' +
          '<div class="small text-center text-muted mt-1">New upload</div>';
        wrap.appendChild(col);
        var reader = new FileReader();
        reader.onload = function (e) {
          var btn = col.querySelector('.nb-cover-btn');
          if (btn) {
            btn.innerHTML =
              '<img src="' + e.target.result + '" alt="" class="w-100" style="height:100px;object-fit:cover">';
          }
        };
        reader.readAsDataURL(files[j]);
        col.querySelector('.nb-cover-btn').addEventListener('click', function () {
          nbSetCover(idx);
        });
      })(i);
    }
    recalcCoverMax();
  }

  document.addEventListener('DOMContentLoaded', function () {
    var ownerForm = document.getElementById('nbOwnerPropertyForm');
    if (ownerForm) {
      ownerForm.addEventListener('submit', function (ev) {
        if (typeof window.fetch !== 'function') {
          return;
        }
        ev.preventDefault();
        var saveUrl = ownerForm.getAttribute('data-save-url') || ownerForm.action;
        var successUrl = ownerForm.getAttribute('data-success-url') || '';
        var errBox = document.getElementById('nbOwnerSaveError');
        var btn = ownerForm.querySelector('button[type="submit"]');
        if (btn) {
          btn.disabled = true;
        }
        if (errBox) {
          errBox.classList.add('d-none');
          errBox.textContent = '';
        }
        var headers = { Accept: 'application/json' };
        try {
          var token = localStorage.getItem('nb_token');
          if (token) {
            headers['X-Api-Token'] = token;
          }
        } catch (e) {}
        fetch(saveUrl, {
          method: 'POST',
          body: new FormData(ownerForm),
          credentials: 'same-origin',
          headers: headers,
        })
          .then(function (res) {
            return res.json().then(function (data) {
              return { ok: res.ok, data: data };
            }).catch(function () {
              return { ok: false, data: { success: false, message: 'Invalid server response.' } };
            });
          })
          .then(function (result) {
            if (result.data && result.data.success) {
              if (successUrl) {
                window.location.href = successUrl;
              } else {
                window.location.reload();
              }
              return;
            }
            var msg = (result.data && result.data.message) || 'Could not save property.';
            if (result.data && result.data.upload_errors && result.data.upload_errors.length) {
              msg += '\n' + result.data.upload_errors.join('\n');
            }
            if (errBox) {
              errBox.textContent = msg;
              errBox.classList.remove('d-none');
              errBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
              window.alert(msg);
            }
          })
          .catch(function () {
            if (errBox) {
              errBox.textContent = 'Network error while saving. Please try again.';
              errBox.classList.remove('d-none');
            } else {
              window.alert('Network error while saving. Please try again.');
            }
          })
          .finally(function () {
            if (btn) {
              btn.disabled = false;
            }
          });
      });
    }

    var fileInput = qs('#nbPropImages');
    if (fileInput) {
      fileInput.addEventListener('change', buildNewPreviews);
    }
    qsa('.nb-remove-existing').forEach(function (cb) {
      cb.addEventListener('change', function () {
        var row = cb.closest('.nb-prop-img-row');
        if (!row) {
          return;
        }
        var thumb = row.querySelector('.nb-prop-existing-thumb');
        if (cb.checked) {
          row.classList.add('opacity-50');
          if (thumb) {
            thumb.classList.add('d-none');
          }
        } else {
          row.classList.remove('opacity-50');
          if (thumb) {
            thumb.classList.remove('d-none');
          }
        }
        buildNewPreviews();
      });
    });
    qsa('.nb-cover-existing-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var idx = parseInt(btn.dataset.coverIdx, 10);
        if (!isNaN(idx)) {
          nbSetCover(idx);
        }
      });
    });
    highlightCover();
  });
})();
