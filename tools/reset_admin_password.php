<?php
$hash = password_hash('admin123', PASSWORD_BCRYPT);
echo "New hash: " . $hash . "\n";
