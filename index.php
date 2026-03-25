<?php
function isAdmin()
{
    $isAdmin = false;
    if (isset($_COOKIE["adminAuth"])) {
        require_once "./server/context/token.php";

        $token = decryptToken($_COOKIE["adminAuth"]);

        if (
            $token != null && isset($token["email"], $token["pwd"]) &&
            $token["email"] == "email@gmail.com" && $token["pwd"] == "Password1!"
        ) {
            $isAdmin = time() - $token["createdAt"] <= 600000;
        } else {
            $obj["msg"] = "Token non valido!";
        }
    }

    return $isAdmin;
}

function hasAccess($path)
{
    #QUA SI SETTERANNO I VARI CONTROLLI PER LE VARIE PAGINE
    $hasAccess = true;

    switch ($path) {
        case "dashboard":
            $hasAccess = isAdmin();
            break;
    }

    return $hasAccess;
}

if (
    $_SERVER["REQUEST_URI"] != "/" &&
    (
        str_starts_with($_SERVER["REQUEST_URI"], "/secure/")
        || str_starts_with($_SERVER["REQUEST_URI"], "/server/")
    )
) {
    header("Location: /403/index.html");
    die;
}

if (str_contains($_SERVER["REQUEST_URI"], "?")) {
    $uriArr = explode("?", $_SERVER["REQUEST_URI"]);

    $getParams = $uriArr[1];
    $uriVero = $uriArr[0];
} else {
    $uriVero = $_SERVER["REQUEST_URI"];
    $getParams = "";
}

#Se la richiesta è una directory, cerca un index.html al suo interno
if ($_SERVER["REQUEST_URI"] != "/" && is_dir($_SERVER['DOCUMENT_ROOT'] . $uriVero)) {
    $uriVero .= "index.html";
}

$route = explode("/", $uriVero);
$path = $route[count($route) - 2];
$filePath = $_SERVER['DOCUMENT_ROOT'] . $uriVero;

if ($path != "" && file_exists($filePath) && str_ends_with($uriVero, ".html")) {


    if (!hasAccess($path)) {
        #l'utente non ha i permessi per accedere alla pagina, do errore 403
        header("Location: /403/index.html");
        die;
    } else {
        #la pagina esiste e l'utente ha i diritti di utilizzarla
        #resituisco all'utente la pagina
        readfile($filePath);
        die;
    }

}


#NOTA, PUOI FARE ANCHE QUA DELLE HEADER SE NON HAI I DIRITTI PER FARE UNA DETERMINATA RICHIESTA
#NOTA, PUOI FARE ANCHE QUA DELLE HEADER SE NON HAI I DIRITTI PER FARE UNA DETERMINATA RICHIESTA

#QUI SOTTO DEVI METTERE TUTTE LE REDIRECT CHE FAI TRAMITE FETCH/FORM SENNO' NON FUNZIONA
#QUI SOTTO DEVI METTERE TUTTE LE REDIRECT CHE FAI TRAMITE FETCH/FORM SENNO' NON FUNZIONA
#QUI SOTTO DEVI METTERE TUTTE LE REDIRECT CHE FAI TRAMITE FETCH/FORM SENNO' NON FUNZIONA

#qua vanno fatti i reindirizzamenti fatti tramite form
#qua vanno fatti i reindirizzamenti fatti tramite form
#qua vanno fatti i reindirizzamenti fatti tramite form
#FAI CONTROLLI PERMESSI ANCHE QUI
#FAI CONTROLLI PERMESSI ANCHE QUI
#FAI CONTROLLI PERMESSI ANCHE QUI

$route = explode("/", $uriVero);
$path = $route[count($route) - 1];

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (str_contains($_SERVER["REQUEST_URI"], "?") && str_ends_with($uriVero, ".html")) {
        $path = $route[count($route) - 2];
    }

    switch ($path) {
        case "":
            header("Location: /home/index.html");
            die;

        case "vini":
        case "club":
        case "eventi":
        case "event":
        case "vino":
        case "home":
        case "iscrizione":
        case "about":
            header("Location: /$path/index.html$getParams");
            die;

        case "checkPayment":
        case "getPayment":
            require_once "./server/context/$path.php";
            die;

        case "dashboard":
            if (isAdmin()) {
                header("Location: /public/$path/index.html$getParams");
            } else {
                header("Location: /403/index.html");
            }
            die;

        default:
            if (str_starts_with($path, "get")) {
                require_once "./server/modules/apiGet.php";
                die;
            }
    }

} else if ($_SERVER["REQUEST_METHOD"] === "POST") {

    switch ($path) {
        case "adminLogin":
        case "iscrivi":
            require_once "./server/modules/$path.php";
            die;

        case "checkPayment":
            require_once "./server/context/checkPayment.php";
            die;

        default:
            if (!isAdmin()) {
                require_once "./server/modules/apiAdmin.php";
            } else {
                jsonDenyMsg();
            }
            die;
    }
}


#se non è ne una pagina o un file esistente e neanche una "risorsa api"
header("Location: /404/index.html");
die;

function jsonDenyMsg($msg = "Non hai i diritti per accedere a questa funzionalità!")
{
    header("Content-Type: application/json");
    die(json_encode(["msg" => $msg]));
}

function hasKeys(array $data, array $keys)
{
    return empty(array_diff_key(array_flip($keys), $data));
}