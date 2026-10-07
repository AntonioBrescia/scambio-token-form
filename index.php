<?php
require_once __DIR__ . '/functions.php';

app_session_start();

// Nuovo token ad ogni apertura della pagina iniziale
$ttlSeconds = 120;
$token = create_new_token($ttlSeconds);
$expireAt = get_expire_time();
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Index - Scambio Token</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .box { max-width: 700px; border: 1px solid #ccc; padding: 16px; border-radius: 8px; }
        a.button { display: inline-block; padding: 10px 14px; background: #1454d9; color: #fff; text-decoration: none; border-radius: 6px; }
        code { background: #f4f4f4; padding: 2px 5px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Pagina iniziale</h1>
        <p>Token generato. Vai alla pagina con il form.</p>
        <p><strong>Scadenza:</strong> <?php echo h(date('H:i:s', $expireAt)); ?></p>
        <p><a class="button" href="form1.php">Procedi a form1</a></p>
    </div>
</body>
</html>
