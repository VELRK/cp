<?php defined('BASEPATH') OR exit('No direct script access allowed');
$sub_rows = isset($sub_rows) && is_array($sub_rows) ? $sub_rows : array();
$main_rows = isset($main_rows) && is_array($main_rows) ? $main_rows : array();
$toggle_api = site_url('api/property-types/toggle');

function nb_pt_render_row($a, $is_sub, $parent_name, $sub_rows, $toggle_api) {
    $id = (int) $a->id;
    $active = !empty($a->is_active);
    ?>
    <tr data-pt-id="<?php echo $id; ?>" class="<?php echo $is_sub ? 'nb-pt-sub-row' : 'nb-pt-main-row'; ?>">
      <td>
        <?php if ($is_sub) : ?>
          <span class="badge bg-light text-secondary border">Sub</span>
        <?php else : ?>
          <span class="badge bg-primary-subtle text-primary-emphasis">Main</span>
        <?php endif; ?>
      </td>
      <td class="fw-medium">
        <?php if ($is_sub) : ?><span class="text-muted me-1">↳</span><?php endif; ?>
        <?php echo html_escape($a->name); ?>
      </td>
      <td class="small text-muted font-monospace"><?php echo html_escape($a->slug); ?></td>
      <td class="small"><?php echo $is_sub ? html_escape($parent_name) : '—'; ?></td>
      <td><?php echo (int) $a->sort_order; ?></td>
      <td>
        <div class="form-check form-switch m-0">
          <input class="form-check-input nb-pt-active-toggle" type="checkbox" role="switch"
            data-id="<?php echo $id; ?>"
            data-url="<?php echo html_escape($toggle_api); ?>"
            <?php echo $active ? 'checked' : ''; ?>
            aria-label="Toggle active for <?php echo html_escape($a->name); ?>">
        </div>
      </td>
      <td class="text-end text-nowrap">
        <div class="d-inline-flex align-items-center gap-2 flex-nowrap">
          <?php if (!$is_sub) : ?>
            <a class="btn btn-sm btn-outline-success rounded-pill px-3" href="<?php echo site_url('panel/property-type/add-sub/' . $id); ?>">Add sub</a>
          <?php endif; ?>
          <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?php echo site_url('panel/property-type/edit/' . $id); ?>">Edit</a>
          <?php echo form_open(site_url('panel/property-type/delete/' . $id), array('class' => 'd-inline m-0', 'onsubmit' => "return confirm('Delete this property type?');")); ?>
            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">Delete</button>
          <?php echo form_close(); ?>
        </div>
      </td>
    </tr>
    <?php
}
?>
<div class="nb-admin-page-head d-flex flex-wrap justify-content-between align-items-start gap-3">
  <div>
    <h1 class="nb-admin-page-title">Property types</h1>
    <p class="nb-admin-page-desc mb-0">
      Create a <strong>main type</strong> first, then add <strong>sub types</strong> under it.
      Used in listing forms, search filters, and <code>GET /api/property-types</code>.
    </p>
  </div>
  <a class="btn btn-danger rounded-pill px-3" href="<?php echo site_url('panel/property-type/add'); ?>">
    <i class="bi bi-plus-lg me-1"></i> Add main type
  </a>
</div>

<div class="nb-admin-panel">
  <div class="nb-admin-panel-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h2 class="nb-admin-panel-title mb-0">All types</h2>
    <span class="badge bg-light text-dark border"><?php echo count($rows); ?> total</span>
  </div>
  <div class="nb-admin-table-wrap">
    <table class="table nb-admin-table mb-0" id="nbPropertyTypesTable">
      <thead>
        <tr>
          <th>Level</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Main type</th>
          <th>Sort</th>
          <th>Active</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($main_rows) && empty($rows)) : ?>
        <tr>
          <td colspan="7" class="text-center text-muted py-5">
            No property types yet. Run migration <code>028_nb_property_types_hierarchy.sql</code> or click <strong>Add main type</strong>.
          </td>
        </tr>
        <?php else : ?>
          <?php foreach ($main_rows as $main) : ?>
            <?php nb_pt_render_row($main, false, '', $sub_rows, $toggle_api); ?>
            <?php
            $children = isset($sub_rows[(int) $main->id]) ? $sub_rows[(int) $main->id] : array();
            foreach ($children as $sub) :
              nb_pt_render_row($sub, true, $main->name, $sub_rows, $toggle_api);
            endforeach;
            ?>
          <?php endforeach; ?>
          <?php
          // Orphan sub types (parent missing)
          foreach ($rows as $a) :
            if (!empty($a->parent_id) && empty($main_rows)) {
              continue;
            }
            if (!empty($a->parent_id)) {
              $found = false;
              foreach ($main_rows as $m) {
                if ((int) $m->id === (int) $a->parent_id) {
                  $found = true;
                  break;
                }
              }
              if ($found) {
                continue;
              }
              nb_pt_render_row($a, true, '#' . (int) $a->parent_id, $sub_rows, $toggle_api);
            }
          endforeach;
          ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.nb-pt-sub-row td { background: #fafbfc; }
.nb-pt-active-toggle:disabled { opacity: 0.5; cursor: wait; }
</style>
<script>
(function () {
  document.querySelectorAll('.nb-pt-active-toggle').forEach(function (el) {
    el.addEventListener('change', function () {
      var input = this;
      var id = input.getAttribute('data-id');
      var url = input.getAttribute('data-url');
      var prev = !input.checked;
      input.disabled = true;
      var body = new URLSearchParams({ id: id, ajax: '1' });
      fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: body.toString(),
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (!data || !data.success) {
            input.checked = prev;
            alert((data && data.message) ? data.message : 'Could not update status.');
            return;
          }
          input.checked = !!parseInt(data.is_active, 10);
        })
        .catch(function () {
          input.checked = prev;
          alert('Network error — could not update status.');
        })
        .finally(function () { input.disabled = false; });
    });
  });
})();
</script>
