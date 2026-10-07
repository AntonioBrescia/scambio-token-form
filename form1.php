<?php
require_once __DIR__ . '/functions.php';

app_session_start();

$feedback = '';

// Caso "torna indietro" da form2: qui avviene il controllo token richiesto.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'back') {
    $incomingToken = isset($_POST['token2']) ? $_POST['token2'] : '';
    $reason = '';

    if (validate_token($incomingToken, $reason)) {
        $feedback = 'OK - Token valido';
    } else {
        $feedback = 'FALLITO - ' . $reason;
    }

    end_flow_and_cleanup();
}

$token = get_current_token();
$expireAt = get_expire_time();

if ($feedback === '' && ($token === '' || $expireAt <= 0)) {
    header('Location: index.php');
    exit;
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form 1 - Scambio Token</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .box { max-width: 700px; border: 1px solid #ccc; padding: 16px; border-radius: 8px; }
        button { padding: 10px 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Form 1</h1>
        <?php if ($feedback !== ''): ?>
            <p><strong><?php echo h($feedback); ?></strong></p>
            <p><a href="index.php">Torna a index</a></p>
        <?php else: ?>
            <p>Questo form invia il token nascosto alla pagina successiva.</p>
            <p><strong>Scadenza token:</strong> <?php echo h(date('H:i:s', $expireAt)); ?></p>

            <form method="post" action="form2.php">
                <input type="hidden" name="token" value="<?php echo h($token); ?>">
                <button type="submit">Submit</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
