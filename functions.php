<?php
// Funzioni comuni del flusso "scambio token".
// Nessun database: il token vive solo nella sessione PHP, che a fine flusso viene distrutta.
date_default_timezone_set('Europe/Rome');
function app_session_start()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Cookie di sessione "chiuso": non leggibile da JavaScript e non inviato da altri siti
        session_set_cookie_params(array('httponly' => true, 'samesite' => 'Strict'));
        session_start();
    }
}

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------------------------------
// Cifratura "banale" del token
// ---------------------------------------------------------------------
// Il token contiene un id casuale e la scadenza. Il testo viene mescolato (XOR)
// con una chiave casuale che esiste solo nella sessione e poi scritto in base64:
// il risultato e' una stringa illeggibile a colpo d'occhio.
// In un caso reale si userebbe openssl_encrypt() (AES-GCM) o un token firmato (HMAC).

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($text)
{
    $text = strtr($text, '-_', '+/');
    $text .= str_repeat('=', (4 - strlen($text) % 4) % 4);
    return base64_decode($text, true);
}

function xor_text($text, $key)
{
    $out = '';
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($text); $i++) {
        $out .= $text[$i] ^ $key[$i % $keyLen];
    }
    return $out;
}

function encode_token($id, $expireAt, $key)
{
    return base64url_encode(xor_text($id . '|' . $expireAt, $key));
}

// Restituisce array(id, scadenza) oppure null se il token non e' decifrabile
function decode_token($token, $key)
{
    $raw = base64url_decode($token);
    if ($raw === false || $raw === '') {
        return null;
    }

    $parts = explode('|', xor_text($raw, $key));
    if (count($parts) !== 2 || !ctype_digit($parts[1])) {
        return null;
    }
    return array($parts[0], (int)$parts[1]);
}

// ---------------------------------------------------------------------
// Gestione del token
// ---------------------------------------------------------------------

function create_new_token($ttlSeconds)
{
    $key      = random_bytes(16);           // chiave casuale, solo per questo giro
    $id       = bin2hex(random_bytes(12));  // parte casuale che rende il token unico
    $expireAt = time() + (int)$ttlSeconds;

    $_SESSION['flow_key']        = $key;
    $_SESSION['flow_token']      = encode_token($id, $expireAt, $key);
    $_SESSION['flow_expires_at'] = $expireAt;   // serve solo per mostrare l'orario a video

    return $_SESSION['flow_token'];
}

function get_current_token()
{
    if (!isset($_SESSION['flow_token'])) {
        return '';
    }
    return (string)$_SESSION['flow_token'];
}

function get_expire_time()
{
    if (!isset($_SESSION['flow_expires_at'])) {
        return 0;
    }
    return (int)$_SESSION['flow_expires_at'];
}

// Controlla il token ricevuto. Se non e' valido scrive il motivo in $reason.
function validate_token($incomingToken, &$reason)
{
    $reason = '';

    // 1) Vuoto
    $incomingToken = trim((string)$incomingToken);
    if ($incomingToken === '') {
        $reason = 'Token vuoto';
        return false;
    }

    // 2) Niente in sessione (flusso gia' concluso o mai iniziato)
    $sessionToken = get_current_token();
    if ($sessionToken === '' || !isset($_SESSION['flow_key'])) {
        $reason = 'Token sessione assente';
        return false;
    }

    // 3) Diverso da quello generato all'inizio
    if (!hash_equals($sessionToken, $incomingToken)) {
        $reason = 'Token non corrispondente';
        return false;
    }

    // 4) Uguale: lo decifro e leggo la scadenza scritta dentro il token stesso
    $data = decode_token($incomingToken, $_SESSION['flow_key']);
    if ($data === null) {
        $reason = 'Token non valido';
        return false;
    }
    if (time() > $data[1]) {
        $reason = 'Token scaduto';
        return false;
    }

    return true;
}

// ---------------------------------------------------------------------
// Pulizia finale: nessuna traccia in sessione ne' nei cookie
// ---------------------------------------------------------------------

function clear_flow_data()
{
    unset($_SESSION['flow_token']);
    unset($_SESSION['flow_key']);
    unset($_SESSION['flow_expires_at']);
}

function destroy_session_cookie()
{
    if (!ini_get('session.use_cookies')) {
        return;
    }

    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

function end_flow_and_cleanup()
{
    clear_flow_data();
    $_SESSION = array();
    destroy_session_cookie();
    session_destroy();
}
