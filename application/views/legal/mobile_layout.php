<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Coimbatore Properties'; ?></title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f6fa;
            color: #444;
            font-size: 15px;
            line-height: 1.7;
        }

        .page-header {
            background: #1a1a2e;
            color: #fff;
            padding: 20px 16px 16px;
        }

        .page-header h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .content {
            padding: 20px 16px 40px;
            max-width: 720px;
            margin: 0 auto;
        }

        h6 {
            color: #1a1a2e;
            font-size: 14px;
            font-weight: 700;
            margin: 22px 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        p {
            color: #555;
            margin-bottom: 12px;
        }

        ul {
            color: #555;
            padding-left: 18px;
            margin-bottom: 14px;
        }

        li {
            margin-bottom: 4px;
        }

        a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : ''; ?></h1>
    </div>
    <div class="content">
        <?php $this->load->view('legal/' . $legal_view); ?>
    </div>
</body>

</html>