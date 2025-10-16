<?php
if (isset($_COOKIE["paymentData"])) {
    require_once "./server/context/token.php";

    $token = decryptToken($_COOKIE["paymentData"]);

    if ($token != null) {
        require_once "./server/controllers/pagamenti.php";

        $ris = Pagamento::validaPagamentiRegistrati($token["idEvento"]);

        if (!isset($ris["msg"])) {
            if (time() - $token["createdAt"] <= 1209600) {

                require_once "./server/libs/satispay/init.php";

                try {
                    $payment = \SatispayGBusiness\Payment::get($token["paymentId"]);

                    if ($payment->status == "PENDING") {
                        $obj = [
                            "ok" => true,
                            "codeIdentifier" => $payment->code_identifier
                        ];
                    } else if ($payment->status == "ACCEPTED") {
                        $paymentId = Pagamento::getIdIfExists($token["cellulare"], $token["nome"], $token["cognome"], $token["idEvento"]);

                        if (isset($paymentId["msg"])) {
                            $obj["msg"] = $paymentId["msg"];

                        } else if ($paymentId) {
                            #PAGAMENTO ESISTENTE
                            $ris = Pagamento::eliminaPendingByPaymentId($payment->id);

                            if (!isset($ris["msg"])) {
                                // Aggiungi a Iscrizioni
                                require_once "./server/controllers/iscrizioni.php";

                                $ris = Iscrizione::aggiungi(
                                    [
                                        "Cellulare" => $payment->metadata->Cellulare,
                                        "Nome" => $payment->metadata->Nome,
                                        "Cognome" => $payment->metadata->Cognome,
                                        "IdEvento" => $payment->metadata->IdEvento,
                                        "MetodoPagamento" => 'S',
                                        "PaymentId" => $payment->id,
                                        "Data" => $payment->insert_date
                                    ]
                                );

                                if (!isset($ris["msg"])) {
                                    #PAGAMENTO ACCETTATO ED E' STATO
                                    #AGGIUNTO NELLA TABELLA ISCRIZIONI
                                    #E L'UTENTE E' FINALMENTE ISCRITTO
                                    $obj = [
                                        "ok" => true,
                                        "msg" => "Pagamento già completato! Ti sei iscritto con successo!!",
                                        "paymentAccepted" => true
                                    ];
                                } else {
                                    $obj["msg"] = $ris["msg"];
                                }
                            } else {
                                $obj["msg"] = $ris["msg"];
                            }
                        } else {
                            #PAGAMENTO NON TROVATO NEL DB =>
                            #VUOL DIRE CHE E' GIA' STATO AGGIUNTO NELLA
                            #TABELLA ISCRIZIONI ED E' TUTTO APOSTO
                            $obj = [
                                "ok" => true,
                                "msg" => "Pagamento già completato! Sei già stato inserito tra gli iscritti dell'evento!",
                                "paymentAccepted" => true
                            ];
                        }
                    } else {
                        $ris = Pagamento::eliminaPendingByPaymentId($payment->id);

                        if (!isset($ris["msg"])) {
                            $obj = [
                                "msg" => "Pagamento cancellato o scaduto (oltre 5 minuti). Rigenera pagamento (non è stato addebitato nulla)",
                                "tryAgain" => true
                            ];
                        } else {
                            $obj["msg"] = $ris["msg"];
                        }
                    }
                } catch (Exception $e) {
                    $obj = [
                        "msg" => "Impossibile recuperare pagamento. Riprova ad iscriverti. (" . $e->getMessage() . ")",
                        "tryAgain" => true
                    ];
                }
            } else {
                $obj = [
                    "msg" => "Pagamento scaduto (oltre 1 mese...). Rigenera pagamento. Se vedi questo messaggio e hai già inviato il pagamento contattaci per risolvere.",
                    "tryAgain" => true
                ];
            }
        } else {
            $obj["msg"] = $ris["msg"];
        }
    } else {
        $obj["msg"] = "Token non valido!";
    }
} else {
    $obj = [
        "msg" => "Potresti aver cancellato i cookie. Se non hai inviato il pagamento entro 1 mese potrebbe non essere stato validato correttamente. Contattaci per risolvere.",
        "tryAgain" => true
    ];
}

header("Content-Type: application/json");
echo json_encode($obj);