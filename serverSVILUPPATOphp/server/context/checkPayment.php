<?php
require_once "./server/libs/satispay/init.php";

if (isset($_COOKIE["paymentData"])) {
    require_once "./server/context/token.php";

    $token = decryptToken($_COOKIE["paymentData"]);

    if ($token != null) {
        if (time() - $token["createdAt"] <= 1209600) {
            try {
                $payment = \SatispayGBusiness\Payment::get($token["paymentId"]);

                if ($payment->status == "PENDING") {
                    $obj = [
                        "ok" => true,
                        "status" => $payment->status
                    ];
                } else if ($payment->status == "CANCELED" || $payment->status == "EXPIRED") {
                    require_once "./server/controllers/pagamenti.php";

                    $ris = Pagamento::eliminaPendingByPaymentId($token["paymentId"]);

                    if (!isset($ris["msg"])) {
                        $obj = [
                            "ok" => true,
                            "status" => $payment->status
                        ];
                    } else {
                        $obj["msg"] = $ris["msg"];
                    }
                } else if ($payment->status == "ACCEPTED") {
                    require_once "./server/controllers/pagamenti.php";
                    #ULTERIORE CONTROLLO PER NON ISCRIVERE DUE VOLTE
                    $paymentInfo = Pagamento::getIdIfExists(
                        $payment->metadata->Cellulare,
                        $payment->metadata->Nome,
                        $payment->metadata->Cognome,
                        $payment->metadata->IdEvento
                    );

                    if (!isset($paymentInfo["msg"])) {
                        if ($paymentInfo) {
                            #RIMUOVO DAL DB E AGGIUNGO NELLA TABELLA ISCRIZIONI
                            require_once "./server/controllers/iscrizioni.php";

                            $ris = Pagamento::eliminaPendingByPaymentId($payment->id);

                            if (!isset($ris["msg"])) {
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
                                    $obj = [
                                        "ok" => true,
                                        "msg" => "Pagamento ed iscrizione sono andati a buon fine!",
                                        "status" => "ACCEPTED"
                                    ];
                                } else {
                                    $obj["msg"] = "Pagamento addebitato ma c'è stato un' errore, comunica il codice errore e verrà risolto il problema (03)  " . $ris["msg"];
                                }
                            } else {
                                $obj["msg"] = "Pagamento addebitato ma c'è stato un' errore, comunica il codice errore e verrà risolto il problema (02)  " . $ris["msg"];
                            }
                        } else {
                            #GIA REGISTRATO
                            $obj = [
                                "ok" => true,
                                "status" => "ACCEPTED",
                                "msg" => "Pagamento già completato e iscritto."
                            ];
                        }
                    } else {
                        $obj["msg"] = "Pagamento addebitato ma c'è stato un' errore, comunica il codice errore e verrà risolto il problema (01)  " . $paymentInfo["msg"];
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
        $obj["msg"] = "Token non valido!";
    }
} else {
    $obj = [
        "msg" => "Tempo scaduto (oltre 5 minuti). Rigenera pagamento",
        "tryAgain" => true
    ];
}

header("Content-Type: application/json");
echo json_encode($obj);
