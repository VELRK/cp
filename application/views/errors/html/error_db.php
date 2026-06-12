<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Database Error</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
        .box { border: 1px solid #ddd; border-radius: 6px; padding: 16px; background: #fafafa; }
        h1 { margin-top: 0; font-size: 22px; }
        pre { white-space: pre-wrap; word-break: break-word; background: #fff; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>A Database Error Occurred</h1>
        <?php if (isset($heading)) : ?>
            <p><strong><?php echo $heading; ?></strong></p>
        <?php endif; ?>
        <?php if (isset($message)) : ?>
            <pre><?php echo $message; ?></pre>
        <?php endif; ?>
    </div>
</body>
</html>
