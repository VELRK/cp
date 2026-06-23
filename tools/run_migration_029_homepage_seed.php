<?php
/**
 * Seed homepage section flags + sub property types for category counts.
 * Run: php tools/run_migration_029_homepage_seed.php
 */
$m = new mysqli('127.0.0.1', 'root', '', 'property', 3306);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}
$m->set_charset('utf8mb4');

function col_exists(mysqli $m, $table, $col)
{
    $table = $m->real_escape_string($table);
    $col = $m->real_escape_string($col);
    $r = $m->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    return $r && $r->num_rows > 0;
}

foreach (array('is_recommended', 'is_newly_launched', 'is_verified_property') as $col) {
    if (!col_exists($m, 'nb_properties', $col)) {
        fwrite(STDERR, "Missing column nb_properties.$col — run migration 025 first.\n");
        exit(1);
    }
}

// Ensure parent_id on property types
$r = $m->query("SHOW COLUMNS FROM nb_property_types LIKE 'parent_id'");
if (!$r || $r->num_rows === 0) {
    fwrite(STDERR, "Missing parent_id on nb_property_types — run migration 028 first.\n");
    exit(1);
}

// Pick a main type for sub categories (prefer apartment, else first main)
$mainId = null;
$mainSlug = null;
$res = $m->query("SELECT id, slug FROM nb_property_types WHERE is_active = 1 AND (parent_id IS NULL OR parent_id = 0) ORDER BY sort_order, id LIMIT 1");
if ($res && ($row = $res->fetch_assoc())) {
    $mainId = (int) $row['id'];
    $mainSlug = $row['slug'];
}
$res = $m->query("SELECT id, slug FROM nb_property_types WHERE slug = 'apartment' AND (parent_id IS NULL OR parent_id = 0) LIMIT 1");
if ($res && ($row = $res->fetch_assoc())) {
    $mainId = (int) $row['id'];
    $mainSlug = $row['slug'];
}

$subTypes = array(
    array('Residential Apartment', 'residential_apartment', 10),
    array('Independent Villa', 'independent_villa', 20),
    array('Builder Floor', 'builder_floor', 30),
    array('Residential Plot', 'residential_plot', 40),
    array('Farm House', 'farm_house', 50),
);

if ($mainId) {
    echo "Using main type id=$mainId slug=$mainSlug\n";
    $stmt = $m->prepare('INSERT IGNORE INTO nb_property_types (name, slug, parent_id, sort_order, is_active) VALUES (?, ?, ?, ?, 1)');
    foreach ($subTypes as $st) {
        $stmt->bind_param('ssii', $st[0], $st[1], $mainId, $st[2]);
        $stmt->execute();
        echo "sub type: {$st[1]}\n";
    }
    $stmt->close();
}

// Active property IDs
$ids = array();
$res = $m->query('SELECT id FROM nb_properties WHERE is_active = 1 ORDER BY id ASC LIMIT 12');
while ($res && ($row = $res->fetch_assoc())) {
    $ids[] = (int) $row['id'];
}

if (empty($ids)) {
    echo "No active properties to seed.\n";
    exit(0);
}

// Reset flags on sample set then assign
$m->query('UPDATE nb_properties SET is_recommended = 0, is_newly_launched = 0, is_verified_property = 0 WHERE id IN (' . implode(',', $ids) . ')');

$recommended = array_slice($ids, 0, min(4, count($ids)));
$newly = array_slice($ids, min(2, count($ids) - 1), min(3, count($ids)));
$verified = array_slice($ids, min(4, count($ids) - 1), min(3, count($ids)));

if (!empty($recommended)) {
    $m->query('UPDATE nb_properties SET is_recommended = 1 WHERE id IN (' . implode(',', $recommended) . ')');
    echo 'is_recommended: ' . implode(',', $recommended) . PHP_EOL;
}
if (!empty($newly)) {
    $m->query('UPDATE nb_properties SET is_newly_launched = 1 WHERE id IN (' . implode(',', $newly) . ')');
    echo 'is_newly_launched: ' . implode(',', $newly) . PHP_EOL;
}
if (!empty($verified)) {
    $m->query('UPDATE nb_properties SET is_verified_property = 1 WHERE id IN (' . implode(',', $verified) . ')');
    echo 'is_verified_property: ' . implode(',', $verified) . PHP_EOL;
}

// Assign sub type slugs to active properties for category counts
$subSlugs = array('residential_apartment', 'independent_villa', 'builder_floor', 'residential_plot', 'farm_house');
$i = 0;
foreach ($ids as $pid) {
    $slug = $subSlugs[$i % count($subSlugs)];
    $stmt = $m->prepare('UPDATE nb_properties SET property_type = ? WHERE id = ?');
    $stmt->bind_param('si', $slug, $pid);
    $stmt->execute();
    $stmt->close();
    $i++;
}
echo "Assigned sub property_type slugs to " . count($ids) . " listings.\n";
echo "DONE\n";
