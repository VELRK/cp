<?php
$m = new mysqli('127.0.0.1', 'root', '', 'property', 3306);
if ($m->connect_error) {
    fwrite(STDERR, $m->connect_error . PHP_EOL);
    exit(1);
}

$statements = array(
    "ALTER TABLE `nb_properties` ADD COLUMN `is_home_banner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_premium`",
    "ALTER TABLE `nb_properties` ADD COLUMN `home_banner_image` VARCHAR(512) DEFAULT NULL AFTER `is_home_banner`",
);

foreach ($statements as $sql) {
    if (!$m->query($sql)) {
        if ((int) $m->errno === 1060) {
            echo "skip (exists): $sql\n";
            continue;
        }
        fwrite(STDERR, $m->error . PHP_EOL);
        exit(1);
    }
    echo "ok: $sql\n";
}

$res = $m->query("SHOW COLUMNS FROM nb_properties LIKE 'is_home_banner'");
echo ($res && $res->num_rows > 0) ? "DONE\n" : "FAILED\n";
