<?php
/* Copiare in config.php (non va su git) e compilare.
   password_hash: php -r "echo password_hash('LA_PASSWORD', PASSWORD_DEFAULT);"
   totp_segreto:  32 lettere A-Z e cifre 2-7 (l'area interna ne propone una dopo il primo accesso); inserirlo
                  nell'app di autenticazione come chiave «basata sul tempo». */
return [
    'password_hash' => '',
    'totp_segreto'  => '',
];
