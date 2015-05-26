<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <title>Home</title>
</head>
<body>

    <h1>Hello <?php echo $name; ?> !</h1>

    <?php if(!isset($_GET['m'])){ ?>
    <p><a href="./Test/console/id/123">Goto Test Url</a></p>
    <?php } ?>

    <p>Please use Firebug / Chrome Developer Tool to view debug message.</p>
</body>
</html>
