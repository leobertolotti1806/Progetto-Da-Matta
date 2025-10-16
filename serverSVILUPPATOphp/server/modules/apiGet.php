<?php
$obj = [];

switch ($path) {
    case "getEvento":
        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
            require_once "./server/controllers/eventi.php";

            $evento = Evento::getById(
                $_GET["id"],
                isset($_GET["minimal"])
            );

            if (!isset($evento["msg"])) {
                $obj = ["ok" => true, "evento" => $evento];
            } else {
                $obj["msg"] = $evento["msg"];
            }

            if (!isset($obj["msg"])) {
                if (isset($_GET["getPostiDisponibili"])) {
                    require_once "./server/controllers/iscrizioni.php";

                    $posti = Iscrizione::getPostiDisponibili($_GET["id"], $evento["Costo"], $evento["PostiTotali"]);

                    if (!isset($posti["msg"])) {
                        $obj["posti"] = $posti;
                    } else {
                        $obj["msg"] = $posti["msg"];
                    }
                }
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getVini":
        if (hasKeys($_GET, ["firstTime", "search", "Marca", "colore", "effervescenza", "sortBy", "order", "limit", "offset"])) {

            require_once "./server/controllers/vini.php";

            if ($_GET["firstTime"]) {
                $offerte = Vino::getViniEvidenziati();

                if (!isset($offerte["msg"])) {
                    $obj["inEvidenzia"] = $offerte;
                } else {
                    $obj["msg"] = $offerte["msg"];
                }
            }

            if (!isset($obj["msg"])) {
                $vini = Vino::getVini($_GET);

                if (!isset($vini["msg"])) {
                    $obj["ok"] = true;
                    $obj["vini"] = $vini;
                } else {
                    $obj = ["msg" => $vini["msg"]];
                }
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getVino":
        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
            require_once "./server/controllers/vini.php";

            $vino = Vino::getById(
                $_GET["id"],
                isset($_GET["minimal"])
            );

            if (!isset($vino["msg"])) {
                $obj = ["ok" => true, "vino" => $vino];
            } else {
                $obj["msg"] = $vino["msg"];
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getParagraphs":
        if (isset($_GET["page"])) {
            require_once "./server/controllers/paragrafi.php";
            if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

                $paragrafo = Paragrafo::getById($_GET["id"]);

                if (!isset($paragrafo["msg"]) && isset($paragrafo["Testo"])) {
                    $obj = ["ok" => true, "paragrafo" => $paragrafo];
                } else {
                    $obj["msg"] = $paragrafo["msg"] ?? "Attributo testo non settato (non trovato) quindi nella ricezione del paragrafo con id " . $_GET["id"];
                }
            } else {
                $paragrafi = Paragrafo::getAllbyPage($_GET["page"]);

                if (!isset($paragrafi["msg"])) {
                    $obj = ["ok" => true, "paragrafi" => $paragrafi];
                } else {
                    $obj["msg"] = $paragrafi["msg"];
                }
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getContatti":
        require_once "./server/controllers/paragrafi.php";
        $contatti = Paragrafo::getAllbyPage("footer");

        if (!isset($contatti["msg"])) {
            $obj = ["ok" => true, "paragrafi" => $contatti];
        } else {
            $obj["msg"] = $contatti["msg"];
        }
        break;

    case "getIscrizione":
        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
            require_once "./server/controllers/iscrizioni.php";
            $sub = Iscrizione::getById($_GET["id"]);


            if (!isset($sub["msg"])) {
                $obj = ["ok" => true, "iscrizione" => $sub];
            } else {
                $obj["msg"] = $sub["msg"];
            }
        } else {
            $obj["msg"] = "Parametri non validi!";
        }
        break;

    case "getEventi":
        require_once "./server/controllers/eventi.php";
        $eventi = Evento::getEventi($_GET);

        if (!isset($eventi["msg"])) {
            $obj = ["ok" => true, "eventi" => $eventi];
        } else {
            $obj["msg"] = $eventi["msg"];
        }
        break;

    default:
        $obj = ["msg" => "Risorsa non trovata!"];
}

header("Content-Type: application/json");
echo json_encode($obj);