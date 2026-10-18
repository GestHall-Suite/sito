<?php
/* Configurazione dell'area interna (non va su git).
   Il modo più semplice: la crea la pubblicazione automatica di GitHub (segreti INTERNO_PASSWORD e INTERNO_TOTP, vedi README).
   A mano: copiare in config.php e compilare.
   - password_hash: generarlo con   php -r 'echo password_hash("LA_PASSWORD", PASSWORD_DEFAULT), PHP_EOL;'
     (apici SINGOLI attorno al comando, altrimenti la shell modifica i caratteri come $ o !) e incollarlo qui
     tra apici SINGOLI: con le virgolette doppie PHP «mangia» i $ dell'hash e la password non funziona più.
   - totp_segreto (facoltativo): 32 lettere A-Z e cifre 2-7 (l'area interna ne propone una dopo il primo accesso);
     inserirlo nell'app di autenticazione come chiave «basata sul tempo». Se è vuoto basta la password. */
return [
    'password_hash' => '',
    'totp_segreto'  => '',
];
