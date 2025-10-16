<?php
require_once "./server/context/connessioneDb.php";

class Pagamento extends ConnessioneDb
{
    public static function esistePagamentoSuSatispay($paymentId)
    {
        require_once "./server/libs/satispay/init.php";

        try {
            $payment = \SatispayGBusiness\Payment::get($paymentId);
            return $payment->status;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getIdIfExists($cellulare, $nome, $cognome, $idEvento)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT PaymentId, Id 
            FROM PendingPagamenti 
            WHERE Cellulare = :cellulare AND IdEvento = :idEvento AND Cognome = :cognome
            AND Nome = :nome");

            $stmt->bindValue(":idEvento", $idEvento, PDO::PARAM_INT);
            $stmt->bindValue(":cellulare", strtoupper($cellulare));
            $stmt->bindValue(":nome", strtoupper($nome));
            $stmt->bindValue(":cognome", strtoupper($cognome));
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function aggiungiPending($cellulare, $nome, $cognome, $idEvento, $paymentId)
    {
        try {
            $stmt = self::getConn()->prepare("INSERT INTO PendingPagamenti 
            (Cellulare, IdEvento, PaymentId, Nome, Cognome) VALUES 
            (:cellulare, :idevento, :paymentid, :nome, :cognome)");

            $stmt->bindValue(":cellulare", strtoupper($cellulare));
            $stmt->bindValue(":nome", strtoupper($nome));
            $stmt->bindValue(":cognome", strtoupper($cognome));
            $stmt->bindValue(":idevento", $idEvento, PDO::PARAM_INT);
            $stmt->bindValue(":paymentid", $paymentId);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function eliminaPendingByPaymentId($paymentId)
    {
        try {
            $stmt = self::getConn()->prepare("DELETE FROM PendingPagamenti WHERE PaymentId = :paymentid");
            $stmt->bindValue(":paymentid", $paymentId);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function aggiorna($idPending, $paymentId)
    {
        try {
            $stmt = self::getConn()->prepare("UPDATE PendingPagamenti SET PaymentId = :paymentId 
            WHERE Id = :id");

            $stmt->bindValue(":paymentId", $paymentId);
            $stmt->bindValue(":id", $idPending, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getAllByEvento($idEvento)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT PaymentId, Cellulare
            FROM PendingPagamenti 
            WHERE IdEvento = :idEvento");
            $stmt->bindValue(":idEvento", $idEvento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function validaPagamentiRegistrati($idEvento)
    {
        $payments = self::getAllByEvento($idEvento);

        if (!isset($payments["msg"])) {
            if (empty($payments)) {
                return ["ok" => true];
            }

            require_once "./server/libs/satispay/init.php";
            
            $acceptedPayments = [];
            $tempPagamenti = [];

            foreach ($payments as $p) {
                $ris = self::validaPagamento($p["PaymentId"]);
                if (hasKeys($ris, ["ok", "paymentData", "status"])) {
                    
                    if ($ris["status"] == "ACCEPTED") {
                        $acceptedPayments[] = $ris["paymentData"];
                    } else {
                        $tempPagamenti[] = $ris["paymentData"];
                        //SE FALLISCE UNO CONTINUA
                    }
                }
            }
            
            if (count($acceptedPayments) > 0) {
                require_once "./server/controllers/iscrizioni.php";
                $ris = Iscrizione::aggiungiMany($acceptedPayments, $idEvento);

                if (isset($ris["msg"])) {
                    return $ris;
                }

                return ["ok" => true, "paymentData" => $tempPagamenti];
            } else {
                return ["ok" => true, "paymentData" => $tempPagamenti];
            }
        } else {
            return $payments;
        }
    }

    public static function validaPagamento($paymentId)
    {
        $returnObj = [];

        try {
            $satiPayment = \SatispayGBusiness\Payment::get($paymentId);


            if ($satiPayment->status === "CANCELED" || $satiPayment->status === "EXPIRED") {
                self::eliminaPendingByPaymentId($paymentId);

                $returnObj = [
                    "ok" => true,
                    "status" => $satiPayment->status,
                    "paymentData" => [
                        "Data" => $satiPayment->insert_date,
                        "Importo" => number_format($satiPayment->amount_unit / 100, 2, ',', '.'),
                        "Iscritto" => $satiPayment->metadata->Cognome . " " . $satiPayment->metadata->Nome,
                        "Evento" => $satiPayment->metadata->IdEvento,
                        "Tipo" => $satiPayment->type === "TO_BUSINESS" ? "E" : "R",
                        "Cellulare" => $satiPayment->metadata->Cellulare,
                        "Status" => $satiPayment->status
                    ]
                ];
            } else if ($satiPayment->status === "ACCEPTED") {
                self::eliminaPendingByPaymentId($paymentId);

                $returnObj = [
                    "ok" => true,
                    "status" => "ACCEPTED",
                    "paymentData" => [
                        "Data" => $satiPayment->insert_date,
                        "Importo" => number_format($satiPayment->amount_unit / 100, 2, ',', '.'),
                        "Iscritto" => $satiPayment->metadata->Cognome . " " . $satiPayment->metadata->Nome,
                        "Evento" => $satiPayment->metadata->IdEvento,
                        "IdEvento" => $satiPayment->metadata->IdEvento,
                        "Tipo" => $satiPayment->type === "TO_BUSINESS" ? "E" : "R",
                        "Cellulare" => $satiPayment->metadata->Cellulare,
                        "Status" => $satiPayment->status,
                        "Nome" => $satiPayment->metadata->Nome,
                        "Cognome" => $satiPayment->metadata->Cognome,
                        "PaymentId" => $paymentId
                    ]
                ];
            } else {
                #PENDING PAGAMENTI
                $returnObj = [
                    "ok" => true,
                    "status" => $satiPayment->status,
                    "paymentData" => [
                        "Data" => $satiPayment->insert_date,
                        "Importo" => number_format($satiPayment->amount_unit / 100, 2, ',', '.'),
                        "Iscritto" => $satiPayment->metadata->Cognome . " " . $satiPayment->metadata->Nome,
                        "Evento" => $satiPayment->metadata->IdEvento,
                        "Tipo" => $satiPayment->type === "TO_BUSINESS" ? "E" : "R",
                        "Cellulare" => $satiPayment->metadata->Cellulare,
                        "Status" => $satiPayment->status
                    ]
                ];
            }

            return $returnObj;
        } catch (Exception $e) {
            return ["msg" => $e->getMessage()];
        }
    }
}