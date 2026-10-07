# Scambio token tra form (PHP)

Piccola applicazione in PHP che fa viaggiare un **token** attraverso più pagine con form HTML e lo verifica quando torna indietro. **Non usa database**: il token vive solo nella sessione PHP e, a fine procedura, sessione e cookie vengono eliminati.

## Flusso

```
index.php  ──link──►  form1.php  ──POST (hidden token)──►  form2.php
                          ▲                                   │
                          │                       ┌───────────┴───────────┐
                          └──── "<< Submit" ──────┘                       └── "Submit >>" ──► finale.php
                        (verifica il token: OK / FALLITO)                          (esito + pulizia)
```

1. **index.php** genera un token nuovo a ogni apertura (con scadenza) e mostra il link alla pagina successiva.
2. **form1.php** contiene un form con `<input type="hidden">` valorizzato con il token e lo invia a form2.
3. **form2.php** riceve il token e lo mette in un unico form con due tasti submit:
   - **<< Submit** torna a form1, che confronta il token ricevuto con quello generato all'inizio e mostra **OK** oppure **FALLITO** (vuoto, diverso, scaduto);
   - **Submit >>** va alla pagina finale (la terza pagina), che mostra anch'essa l'esito.
4. A fine procedura (in entrambi i casi) i dati di sessione vengono cancellati e il cookie di sessione viene fatto scadere.

## Il token

- **Univoco**: ogni apertura di `index.php` genera un token nuovo (`random_bytes`) e sostituisce il precedente.
- **Poco leggibile**: contiene un id casuale e la scadenza, mescolati (XOR) con una chiave casuale che esiste solo nella sessione, e poi scritti in base64. A colpo d'occhio è una stringa senza senso.
- **Con scadenza**: la scadenza (120 secondi, impostabile in `index.php`) è scritta dentro il token stesso; se è passata, il messaggio finale è *Token scaduto*.
- Confronto con `hash_equals`.

> Lo XOR è una cifratura volutamente banale, adatta a questo esercizio. In un caso reale si userebbe `openssl_encrypt()` (AES-GCM) oppure un token firmato con HMAC.

## File

| File | Compito |
|---|---|
| `index.php` | Pagina iniziale, genera il token |
| `form1.php` | Form con il token nascosto; riceve il ritorno e fa la verifica |
| `form2.php` | Pagina intermedia con i due tasti submit |
| `finale.php` | Terza pagina, mostra l'esito |
| `functions.php` | Funzioni comuni: sessione, token, verifica, pulizia |
| `avvia_server.bat` | Avvia il server PHP integrato su Windows |

## Requisiti e avvio

- PHP 7.4 o superiore (provato con PHP 8.3). Non servono estensioni né database.
- Da terminale, nella cartella del progetto:

```bat
php -S localhost:8000
```

oppure doppio clic su `avvia_server.bat`. Poi apri `http://localhost:8000/index.php`.

## Come provarlo

| Caso | Cosa fare | Risultato atteso |
|---|---|---|
| Token corretto | index → form1 → Submit → **<< Submit** | OK - Token valido |
| Token vuoto | In form2 apri gli strumenti sviluppatore (F12), svuota il campo nascosto `token2` e premi **<< Submit** | FALLITO - Token vuoto |
| Token diverso | Come sopra, ma cambia qualche carattere del valore | FALLITO - Token non corrispondente |
| Token scaduto | Attendi più di 120 secondi in form2 (per provare prima, metti `$ttlSeconds = 10;` in `index.php`) e premi un tasto | FALLITO - Token scaduto |
| Fine procedura | Dopo l'esito, nelle impostazioni del browser controlla i cookie | Nessun cookie `PHPSESSID` rimasto |

## Note

- Il cookie di sessione è `HttpOnly` e `SameSite=Strict`.
- L'esito mostrato in `finale.php` arriva come parametro dell'indirizzo e serve solo a visualizzarlo: la pagina finale è un segnaposto, il controllo vero avviene prima del reindirizzamento.
- Se l'utente apre `index.php` e abbandona il flusso senza concluderlo, la sessione resta sul server finché PHP non la elimina da solo.

## Autore

Antonio Brescia
