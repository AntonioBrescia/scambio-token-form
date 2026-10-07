<?php
$result = isset($_GET['result']) ? $_GET['result'] : '';
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$isOk = ($result === 'ok');
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagina finale</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .box { max-width: 760px; border: 1px solid #ccc; padding: 16px; border-radius: 8px; }
        .ok { color: #116b11; }
        .ko { color: #a30000; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Pagina finale</h1>
        <?php if ($isOk): ?>
            <p class="ok"><strong>OK - <?php echo h($msg); ?></strong></p>
        <?php else: ?>
            <p class="ko"><strong>FALLITO - <?php echo h($msg); ?></strong></p>
        <?php endif; ?>

        <p><a href="index.php">Riparti da index</a></p>
    </div>
</body>
</html>
