<?php
require_once "./server/controllers/pagamenti.php";

$paymentInfo = Pagamento::getIdIfExists($data["cellulare"], $data["nome"], $data["cognome"], $data["idEvento"]);

if (!isset($paymentInfo["msg"])) {
    require_once "./server/libs/satispay/init.php";
    require_once "./server/context/token.php";

    if (isset($paymentInfo["PaymentId"])) {
        $paymentId = $paymentInfo["PaymentId"];

        try {
            $checkPayment = \SatispayGBusiness\Payment::get($paymentId);

            if (!isset($ris["msg"])) {
                if ($checkPayment->status == "ACCEPTED") {

                    $ris = Pagamento::eliminaPendingByPaymentId($checkPayment->id);

                    if (!isset($ris["msg"])) {
                        $data["MetodoPagamento"] = 'S';
                        $data["PaymentId"] = $checkPayment->id;
                        $data["Data"] = $checkPayment->insert_date;
                        $ris = Iscrizione::aggiungi($data);

                        if (!isset($ris["msg"])) {
                            #PAGAMENTO ACCETTATO ED E' STATO
                            #AGGIUNTO NELLA TABELLA ISCRIZIONI
                            #E L'UTENTE E' ISCRITTO
                            $obj = [
                                "ok" => true,
                                "msg" => "Pagamento già completato! Ti sei iscritto con successo!!",
                                "paymentAccepted" => true
                            ];

                            deleteToken("paymentData");
                        } else {
                            $obj["msg"] = $ris["msg"];
                        }
                    } else {
                        $obj["msg"] = $ris["msg"];
                    }
                }
            } else {
                $obj["msg"] = $ris["msg"];
            }
        } catch (Exception $e) {
            #OK, INTEORIA SE VA QUA E' PERCHE NON DOVREBBE ESISTERE
        }
    } else {
        #NON HO ANCORA NESSUNO REGISTRATO CON QUESTI DATI
    }

    if (empty($obj)) {
        try {
            $newPayment = \SatispayGBusiness\Payment::create([
                "flow" => "MATCH_CODE",
                "amount_unit" => $evento["Costo"] * 100,
                "currency" => "EUR",
                "metadata" => [
                    "Origin" => "MyApplicationDaMatta",
                    "Cellulare" => strtoupper($data["cellulare"]),
                    "IdEvento" => $data["idEvento"],
                    "Cognome" => strtoupper($data["cognome"]),
                    "Nome" => strtoupper($data["nome"])
                ],
                "expiration_date" => (new DateTime('now', new DateTimeZone('UTC')))
                    ->add(new DateInterval('P1M'))
                    ->format('Y-m-d\TH:i:s.v\Z'),
                "description" => strtoupper($data["cognome"]) . " " . strtoupper($data["nome"]) . " - " . $evento["Titolo"],
                "external_code" => "Iscrizione " . strtoupper($data["cognome"]) . " " . strtoupper($data["nome"]) . " a " . $evento["Titolo"]
            ]);

            if ($paymentInfo) {
                $ris = Pagamento::aggiorna(
                    $paymentInfo["Id"],
                    $newPayment->id
                );
            } else {
                $ris = Pagamento::aggiungiPending(
                    $data["cellulare"],
                    $data["nome"],
                    $data["cognome"],
                    $data["idEvento"],
                    $newPayment->id
                );
            }

            if (!isset($ris["msg"])) {
                setToken("paymentData", [
                    "paymentId" => $newPayment->id,
                    "idEvento" => $data["idEvento"],
                    "cellulare" => strtoupper($data["cellulare"]),
                    "cognome" => strtoupper($data["cognome"]),
                    "nome" => strtoupper($data["nome"])
                ], false);

                $obj = ["ok" => true, "redirectTo" => "/satispay"];
            } else {
                $obj["msg"] = $ris["msg"];
            }
        } catch (Exception $e) {
            $obj["msg"] = $e->getMessage();
        }
    }
} else {
    $obj["msg"] = $paymentInfo["msg"];
}