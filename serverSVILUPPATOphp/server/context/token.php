<?php
/**
 * Cifra un array di dati in un token sicuro e compatto.
 *
 * @param array $data Dati da includere nel token (es. email, timestamp).
 * @param string $key Chiave segreta usata per la cifratura AES-256-CBC.
 *
 * @return string Token cifrato in formato base64, contenente IV + dati cifrati.
 */
function encryptToken($data)
{
    $key = file_get_contents(rtrim($_SERVER["DOCUMENT_ROOT"], '/') . "/secure/token.key");
    $iv = random_bytes(16);
    $cipher = openssl_encrypt(json_encode($data), "AES-256-CBC", $key, 0, $iv);
    return base64_encode($iv . $cipher);
}
/**
 * Decifra un token cifrato precedentemente da encryptToken().
 *
 * @param string $token Token cifrato (in formato base64) ricevuto via email o altro canale.
 * @param string $key Chiave segreta usata per la decifratura (deve essere la stessa usata in encryptToken).
 *
 * @return array|null Restituisce l’array associativo dei dati originali se valido, oppure null se errore.
 */
function decryptToken($token)
{
    $key = file_get_contents(rtrim($_SERVER["DOCUMENT_ROOT"], '/') . "/secure/token.key");
    $data = base64_decode($token);
    $iv = substr($data, 0, 16);
    $cipher = substr($data, 16);
    $decrypted = openssl_decrypt($cipher, "AES-256-CBC", $key, 0, $iv);
    return json_decode($decrypted, true);
}

/**
 * Decifra un token cifrato precedentemente da encryptToken().
 *
 * @param string $name Nome che identificherà il token dai $_COOKIE[$name]
 * @param array $data Contenuto mettere nel token
 * @param int $expire Contenuto mettere nel token
 */
function setToken($name, $data, $expire = false)
{
    $time = time();

    $data["createdAt"] = $time;

    setcookie(
        $name,
        json_encode(encryptToken($data)),
        $time + ((is_bool($expire) && !$expire) ? 1209600 : $expire),
        "/",
        "",
        isset($_SERVER["HTTPS"]),
        true
    );
}

/**
 * Elimina un token precedentemente impostato con setToken().
 *
 * @param string $name Nome del token da eliminare (corrisponde al nome del cookie)
 */
function deleteToken($name)
{
    if (isset($_COOKIE[$name])) {
        // Imposta la scadenza nel passato per rimuovere il cookie
        setcookie(
            $name,
            '',
            time() - 3600, // 1 ora fa
            '/',
            '',
            isset($_SERVER['HTTPS']),
            true
        );

        // Rimuove anche dalla superglobale per evitare accessi successivi nello stesso script
        unset($_COOKIE[$name]);
    }
}
