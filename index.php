<?php
/*
Path: index.php
*/

$url = 'https://www.example.com';

if (!isset($_GET['iframe']) || $_GET['iframe'] !== 'true') {
    header('Location: ' . $url);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Iframe Example</title>
</head>
<body>
    <iframe src="<?php echo $url; ?>" width="100%" height="600"></iframe>
</body>
</html>