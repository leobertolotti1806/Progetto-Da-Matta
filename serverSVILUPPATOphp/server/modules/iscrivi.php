<?php

$obj = [];

$data = json_decode(file_get_contents("php://input"), true);

if (hasKeys($data, ["cellulare", "idEvento", "cognome", "nome"])) {
    require_once "./server/controllers/eventi.php";

    $evento = Evento::getById($data["idEvento"], true);

    if (!isset($evento["msg"])) {
        require_once "./server/controllers/iscrizioni.php";

        $postiDisponibili = Iscrizione::getPostiDisponibili($data["idEvento"]);

        if (!isset($postiDisponibili["msg"])) {
            if ($postiDisponibili > 0) {

                $today = date('Y-m-d');
                if (
                    $evento["ScadenzaIscrizione"] != null && (
                        $today <= $evento["ScadenzaIscrizione"] ||
                        $today <= $evento["Data"]
                    )
                ) {

                    $params = [
                        "Nome" => $data["nome"],
                        "Cognome" => $data["cognome"],
                        "Cellulare" => $data["cellulare"],
                        "IdEvento" => $data["idEvento"],
                    ];

                    $isSubscribed = Iscrizione::alreadySubscribed($params);

                    if (!isset($isSubscribed["msg"])) {
                        if (!$isSubscribed) {
                            #NON ANCORA ISCRITTO!
                            if ($evento["Costo"] > 0) {
                                require_once "./server/context/createPayment.php";
                            } else {
                                $params["MetodoPagamento"] = '/';
                                $params["Data"] = date('Y-m-d H:i:s');

                                $ris = Iscrizione::aggiungi($params);

                                if (!isset($ris["msg"])) {
                                    $obj = ["ok" => true, "redirectTo" => "/success"];
                                } else {
                                    $obj["msg"] = $ris["msg"];
                                }
                            }
                        } else {
                            #UTENTE GIA ISCRITTO!
                            $obj = [
                                "ok" => true,
                                "alreadySubscribed" => true,
                                "msg" => strtoupper($data["nome"]) . " " . strtoupper($data["cognome"]) . " è già registrato/a all'evento."
                            ];

                            if ($evento["Costo"] > 0) {
                                $obj["msg"] .= "La quota di iscrizione è già stata versata " . ($isSubscribed == 'S' ? "su Satispay" : "in contanti");
                            }
                        }
                    } else {
                        $obj["msg"] = $isSubscribed["msg"];
                    }
                } else {
                    $obj["msg"] = "Ci dispiace ma " .
                        (
                            $today <= $evento["ScadenzaIscrizione"] ?
                            "l'iscrizione all'evento era prevista entro " . $evento["ScadenzaIscrizione"]
                            : "non è possibile iscriversi ad un evento passato!"
                        );
                }
            } else {
                $obj["msg"] = "Ci dispiace ma l'evento ha raggiunto il numero massimo di posti disponibili." .
                    (
                        $evento["Costo"] > 0 ?
                        " Potrebbero esserci dei pagamenti in sospeso che occupano posti. Riprova più tardi, magari se n'è liberato uno!"
                        : ""
                    );
            }
        } else {
            $obj["msg"] = $postiDisponibili["msg"];
        }
    } else {
        $obj["msg"] = $evento["msg"];
    }
} else {
    $obj["msg"] = "Parametri non validi!";
}

header("Content-Type: application/json");
echo json_encode($obj);