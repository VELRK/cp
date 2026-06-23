<?php
$m = new mysqli('127.0.0.1', 'root', '', 'property', 3306);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

$statements = array(
    "ALTER TABLE `nb_property_types` ADD COLUMN `parent_id` INT(11) DEFAULT NULL AFTER `id`",
    "ALTER TABLE `nb_property_types` ADD KEY `idx_nb_property_types_parent` (`parent_id`)",
);

foreach ($statements as $sql) {
    if (!$m->query($sql)) {
        if (in_array((int) $m->errno, array(1060, 1061), true)) {
            echo "skip (exists): $sql\n";
            continue;
        }
        fwrite(STDERR, $m->error . PHP_EOL);
        exit(1);
    }
    echo "ok: $sql\n";
}

$res = $m->query("SHOW COLUMNS FROM nb_property_types LIKE 'parent_id'");
echo ($res && $res->num_rows > 0) ? "DONE\n" : "FAILED\n";
