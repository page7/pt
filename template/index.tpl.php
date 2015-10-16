<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <title>Home</title>
</head>
<body>

    <h1><?php echo __('Hello', 'demo') . ' ' . $name; ?> !</h1>

    <p>
    <?php if(!isset($_GET['m'])){ ?>
        <a href="./Test/console/id/123">Goto Test Url</a> |
    <?php } ?>
    <?php if(\pt\framework\language::get() == 'zh_CN') { ?>
        <a href="?lang=en">English</a>
    <?php } else { ?>
        <a href="?lang=zh_CN">中文</a>
    <?php } ?>
    </p>

    <p>Please use Firebug / Chrome Developer Tool (Press F12) to view debug message.</p>
</body>
</html>
