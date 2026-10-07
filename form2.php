<?php
require_once __DIR__ . '/functions.php';

app_session_start();

// Questa pagina si apre solo con un POST proveniente da form1
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Token ricevuto da form1 ("token") oppure rimandato da questo stesso form ("token2")
$tokenFromForm = isset($_POST['token']) ? $_POST['token'] : (isset($_POST['token2']) ? $_POST['token2'] : '');
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Tasto "Submit >>": controlla il token, pulisce tutto e va alla pagina finale
if ($action === 'next') {
    $checkMessage = '';
    $isValid = validate_token($tokenFromForm, $checkMessage);

    $result  = $isValid ? 'ok' : 'ko';
    $message = $isValid ? 'Token valido' : $checkMessage;

    end_flow_and_cleanup();

    header('Location: finale.php?result=' . urlencode($result) . '&msg=' . urlencode($message));
    exit;
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form 2 - Verifica Token</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .box { max-width: 760px; border: 1px solid #ccc; padding: 16px; border-radius: 8px; }
        button { padding: 10px 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Form 2</h1>
        <p>Token ricevuto. Scegli una delle due azioni.</p>

        <!-- Un solo form con due tasti submit: il tasto "<<" ha un suo indirizzo (formaction) -->
        <form method="post" action="form2.php">
            <input type="hidden" name="token2" value="<?php echo h($tokenFromForm); ?>">

            <button type="submit" name="action" value="back" formaction="form1.php">&lt;&lt; Submit</button>
            <button type="submit" name="action" value="next">Submit &gt;&gt;</button>
        </form>
    </div>
</body>
</html>
