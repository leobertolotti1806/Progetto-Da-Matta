<?php
$obj = [];
if (empty($_POST)) {
    $data = json_decode(file_get_contents("php://input"), true);
} else {
    $data = $_POST;
}

switch ($path) {
    case "getAdminEventi":
        if (
            hasKeys($data, ["search", "sortBy", "sortDir", "limit", "offset"])
            &&
            in_array($data["sortBy"], ["", "Titolo", "Data", "ScadenzaIscrizione", "PostiTotali", "Costo", "Iscritti"])
        ) {
            require_once "./server/controllers/eventi.php";
            $eventi = Evento::getAdminEventi($data);

            if (!isset($eventi["msg"])) {
                $obj = ["ok" => true, "data" => $eventi];
            } else {
                $obj["msg"] = $eventi["msg"];
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getAdminIscrizioni":
        if (
            hasKeys($data, ["search", "sortBy", "sortDir", "limit", "offset"])
            &&
            isset($_GET["idEvento"], $_GET["costo"]) && is_numeric($_GET["idEvento"])
            && is_numeric($_GET["costo"])
            &&
            in_array($data["sortBy"], ["", "Nome", "Cognome", "Cellulare", "MetodoPagamento", "Data"])
        ) {
            require_once "./server/controllers/iscrizioni.php";
            $subs = Iscrizione::getAdminIscrizioni($data, $_GET["idEvento"], $_GET["costo"]);

            if (!isset($subs["msg"])) {
                $obj = ["ok" => true, "data" => $subs];
            } else {
                $obj["msg"] = $subs["msg"];
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getAdminVini":
        if (
            hasKeys($data, ["search", "sortBy", "sortDir", "limit", "offset"])
            &&
            in_array($data["sortBy"], ["", "Nome", "Anno", "Costo", "Evidenzia", "Tipo", "Marca", "Denominazione", "Colore", "Effervescenza", "Regione", "Offerta"])
        ) {
            require_once "./server/controllers/vini.php";

            $vini = Vino::getAdminVini($data);

            if (!isset($vini["msg"])) {
                $obj = ["ok" => true, "data" => $vini];
            } else {
                $obj["msg"] = $vini["msg"];
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getDailyPagamenti":
        if (isset($data["date"])) {
            require_once "./server/context/getDailyPagamenti.php";
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;


    case "modVino":
    case "modEvento":
    case "modIscrizione":
    case "modHome":
    case "modEvents":
    case "modContatti":
    case "modAbout":
    case "addVino":
    case "addEvento":
    case "addIscrizione":
    case "delVino":
    case "delEvento":
    case "delIscrizione":

        $shorPath = substr($path, 3);

        $realName = [
            "Vino" => "vini",
            "Evento" => "eventi",
            "Iscrizione" => "iscrizioni",
            "Home" => "paragrafi",
            "About" => "paragrafi",
            "Events" => "paragrafi",
            "Contatti" => "paragrafi"
        ][$shorPath];

        require_once "./server/controllers/$realName.php";

        if ($shorPath == "About" || $shorPath == "Home" || $shorPath == "Events" || $shorPath == "Contatti") {
            $shorPath = "Paragrafo";
        }

        if (str_starts_with($path, "add")) {
            $metodo = "aggiungi";
        } else if (str_starts_with($path, "mod")) {
            $metodo = "modifica";
        } else if (str_starts_with($path, "del") && isset($data["id"])) {
            if ($realName != "iscrizioni") {
                $data = $data["id"];
            }

            $metodo = "elimina";
        }

        $ris = call_user_func([
            $shorPath,
            $metodo
        ], $data);

        if (!isset($ris["msg"])) {
            $obj = ["ok" => true];
        } else {
            $obj["msg"] = $ris["msg"];
        }
        break;

    case "modHomeImages":
    case "modEventsImages":
    case "modLoghiImages":
    case "modAboutImages":
        if (!empty($_FILES)) {
            /* var_dump($_FILES);
            die; */
            foreach ($_FILES as $file) {
                if ($file["error"] !== UPLOAD_ERR_OK) {
                    $obj["msg"] = "Alcuni file non sono stati caricati correttamente. Riprova";
                    break;
                }
            }

            if (!isset($obj["msg"])) {
                require_once "./server/controllers/file.php";

                $folder = strtolower(substr($path, 3, -6));

                foreach ($_FILES as $key => $file) {
                    if (!File::aggiungi($folder, $file, $key)) {
                        $obj["msg"] = "Alcuni file non sono stati convertiti / aggiunti correttamente. Riprova";
                        break;
                    }
                }
            }

            if (!isset($obj["msg"])) {
                $obj["ok"] = true;
            }
        } else {
            $obj["msg"] = "Nessuna modifica effettuata. Nessun file è stato caricato!";
        }
        break;

    default:
        $obj = ["msg" => "Risorsa non trovata!"];
}

header("Content-Type: application/json");
echo json_encode($obj);