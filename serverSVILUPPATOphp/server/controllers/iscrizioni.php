<?php
require_once "./server/context/connessioneDb.php";

class Iscrizione extends ConnessioneDb
{
    public static function aggiungiMany($list, $idEvento)
    {
        $placeholders = [];
        $params = [];

        foreach ($list as $index => $item) {
            $placeholders[] = "(?, ?, ?, ?, ?, ?, ?)";
            $params[] = $idEvento;

            if (hasKeys($item, ["Nome", "Cognome", "Cellulare", "PaymentId", "Data"])) {
                $params[] = strtoupper($item["Nome"]);
                $params[] = strtoupper($item["Cognome"]);
                $params[] = $item["Cellulare"];
                $dateUtc = new DateTimeImmutable($item["Data"], new DateTimeZone("UTC"));
                $dateLocal = $dateUtc->setTimezone(new DateTimeZone("Europe/Rome"));
                $params[] = 'S';
                $params[] = $dateLocal->format("Y-m-d H:i:s");
                $params[] = $item["PaymentId"];
            } else {
                return ["msg" => "Parametri non validi per l'aggiunta delle iscrizioni"];
            }
        }

        try {
            $stmt = self::getConn()->prepare("INSERT INTO Iscrizioni (IdEvento, Nome, Cognome, Cellulare, MetodoPagamento, Data, PaymentId)
            VALUES " . implode(", ", $placeholders));
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function aggiungi($data)
    {
        if (
            !hasKeys($data, ["Nome", "Cognome", "IdEvento", "Cellulare", "MetodoPagamento", "Data"])
            || ($data["MetodoPagamento"] == 'S' && !isset($data["PaymentId"]))
        ) {
            return ["msg" => "Parametri non validi!"];
        }


        if (isset($data["PaymentId"])) {
            require_once "./server/controllers/pagamenti.php";

            $status = Pagamento::esistePagamentoSuSatispay($data["PaymentId"]);

            if (!$status) {
                return ["msg" => "Pagamento non trovato, è possibile che non è stato inserito correttamente il codice pagamento"];
            }

            if ($status != "ACCEPTED") {
                return ["msg" => "È stato trovato il Pagamento ma non è ancora stato validato oppure è scaduto"];
            }
        }

        if (!isset($data["forzaPosti"]) || !$data["forzaPosti"]) {
            $postiDisponibili = self::getPostiDisponibili($data["IdEvento"]);

            if (isset($postiDisponibili["msg"])) {
                return $postiDisponibili;
            }

            if ($postiDisponibili <= 0) {
                return ["msg" => "Posti esauriti o delle iscrizioni sono in atto, si può riprovare tra 5 minuti."];
            }
        }

        $sql = "INSERT INTO Iscrizioni (IdEvento, Nome, Cognome, Cellulare, MetodoPagamento, Data" . (isset($data["PaymentId"]) ? ", PaymentId)" : ")");

        $sql .= "VALUES (:idEvento, :nome, :cognome, :cellulare, :metodoPagamento, :data" . (isset($data["PaymentId"]) ? ", :paymentId)" : ")");

        try {
            $stmt = self::getConn()->prepare($sql);

            $stmt->bindValue(":idEvento", $data["IdEvento"], PDO::PARAM_INT);
            $stmt->bindValue(":nome", strtoupper($data["Nome"]));
            $stmt->bindValue(":cognome", strtoupper($data["Cognome"]));
            $stmt->bindValue(":cellulare", strtoupper($data["Cellulare"]));
            $stmt->bindValue(":data", $data["Data"]);
            $stmt->bindValue(":metodoPagamento", $data["MetodoPagamento"]);

            if (isset($data["PaymentId"])) {
                $stmt->bindValue(":paymentId", $data["PaymentId"]);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function modifica($params)
    {
        if (!hasKeys($params, ["Id", "Nome", "Cognome", "IdEvento", "Cellulare", "MetodoPagamento", "Data"])) {
            return ["msg" => "Parametri non validi!"];
        }

        $id = $params["Id"];
        unset($params["Id"]);
        $setClause = [];

        foreach ($params as $key => $value) {
            $setClause[] = "$key = :$key";
        }

        try {
            $stmt = self::getConn()->prepare("UPDATE Iscrizioni SET " . implode(", ", $setClause) . " WHERE Id = :Id");

            foreach ($params as $param => $value) {
                $stmt->bindValue(":$param", strtoupper($value));
            }

            $stmt->bindValue(":Id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getCostoAndPaymentIdBySubId($id)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT PaymentId, Costo, IdEvento,
            Cellulare, Nome, Cognome 
             FROM Iscrizioni i INNER JOIN Eventi e 
            ON i.IdEvento = e.Id WHERE i.Id = :id");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function elimina($data)
    {
        if (!isset($data["id"])) {
            return ["msg" => "Parametri non validi!"];
        }

        if (isset($data["rimborso"]) && $data["rimborso"]) {
            $paymentInfo = self::getCostoAndPaymentIdBySubId($data["id"]);

            if (isset($paymentInfo["msg"])) {
                return $paymentInfo;
            }

            if (!$paymentInfo || !$paymentInfo["PaymentId"]) {
                return ["msg" => "Non è stato possibile recuperare il pagamento, per favore rimborsare manualmente"];
            }

            require_once "./server/libs/satispay/init.php";

            require_once "./server/controllers/eventi.php";

            $ris = Evento::getById($paymentInfo["IdEvento"], true);

            $titoloEvento = isset($ris["msg"]) ? $paymentInfo["IdEvento"] : $ris["Titolo"];

            try {
                $refund = \SatispayGBusiness\Payment::create([
                    "flow" => "REFUND",
                    "amount_unit" => $paymentInfo["Costo"] * 100,
                    "parent_payment_uid" => $paymentInfo["PaymentId"],
                    "currency" => "EUR",
                    "metadata" => [
                        "Origin" => "MyApplicationDaMatta",
                        "Cellulare" => $paymentInfo["Cellulare"],
                        "IdEvento" => $paymentInfo["IdEvento"],
                        "Cognome" => $paymentInfo["Cognome"],
                        "Nome" => $paymentInfo["Nome"]
                    ],
                    "description" => "Rimborso di " . $paymentInfo["Cognome"] . " " . $paymentInfo["Nome"] . " - " . $titoloEvento,
                    "external_code" => "Rimborso di " . $paymentInfo["Cognome"] . " " . $paymentInfo["Nome"] . " a " . $titoloEvento
                ]);
            } catch (Exception $e) {
                return ["msg" => "Errore rimborso: " . $e->getMessage()];
            }

            if ($refund->status != "ACCEPTED") {
                if ($refund->status === "FAILED") {
                    return ["msg" => "❌ Rimborso fallito. Motivo: " . ($refund->metadata->reason ?? "sconosciuto")];
                } else {
                    return ["msg" => "⏳ Rimborso in stato inatteso: " . $refund->status];
                }
            }
        }

        try {
            $stmt = self::getConn()->prepare("DELETE FROM Iscrizioni WHERE Id = :id");
            $stmt->bindValue(":id", $data["id"], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function eliminaIscrittiEvento($idEvento)
    {
        try {
            $stmt = self::getConn()->prepare("DELETE FROM Iscrizioni WHERE IdEvento = :id");
            $stmt->bindValue(":id", $idEvento, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getById($id)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT Id, Nome, Cognome, Cellulare, IdEvento, Data,
            MetodoPagamento FROM Iscrizioni WHERE Id = :id");

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getAdminIscrizioni($data, $idEvento, $costo)
    {
        if ($costo > 0) {
            require_once "./server/controllers/pagamenti.php";

            $ris = Pagamento::validaPagamentiRegistrati($idEvento);

            if (isset($ris["msg"])) {
                return $ris;
            }
        }

        $sortBy = $data["sortBy"] == "" ? "Cognome" : $data["sortBy"];
        $sortDir = $data["sortDir"] == "" ? "DESC" : $data["sortDir"];
        $limit = intval($data["limit"]);
        $offset = intval($data["offset"]);

        $sql = "SELECT Id, Nome, Cognome, Cellulare,
        CASE WHEN 
            MetodoPagamento = 'S' THEN 'Satispay' 
            WHEN MetodoPagamento = '/' THEN '/'
            ELSE 'Contanti' 
        END AS MetodoPagamento, Data
        FROM Iscrizioni WHERE IdEvento = :idEvento";

        if ($data["search"] != "") {
            $sql .= " AND (Nome LIKE :search OR Cognome LIKE :search OR Cellulare LIKE :search)";
        }

        #IL CONTROLLO PER EVITARE SQL INJECTION E' GIA' STATO FATTO NELL'adminApi.php
        $sql .= " ORDER BY $sortBy $sortDir LIMIT :limit OFFSET :offset";

        try {
            $stmt = self::getConn()->prepare($sql);

            $stmt->bindParam(':idEvento', $idEvento, PDO::PARAM_INT);

            if ($data["search"] !== "") {
                $searchLike = "%" . $data["search"] . "%";
                $stmt->bindParam(':search', $searchLike, PDO::PARAM_STR);
            }

            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getPostiDisponibili($idEvento, $costoEvento = null, $postiTotali = null)
    {
        if ($costoEvento == null && $postiTotali == null) {
            require_once "./server/controllers/eventi.php";

            $evento = Evento::getById($idEvento, true);

            if (isset($evento["msg"])) {
                return $evento;
            }

            $costoEvento = $evento["Costo"];
            $postiTotali = $evento["PostiTotali"];
        }

        $sql = "SELECT 
                (SELECT COUNT(*) FROM Iscrizioni WHERE IdEvento = :idEvento) AS NumIscritti";

        if ($costoEvento > 0) {
            require_once "./server/controllers/pagamenti.php";

            $ris = Pagamento::validaPagamentiRegistrati($idEvento);

            if (isset($ris["msg"])) {
                return $ris;
            }

            $sql .= ",(SELECT COUNT(*) FROM PendingPagamenti WHERE IdEvento = :idEvento) AS NumPending";
        }

        try {
            $stmt = self::getConn()->prepare($sql);
            $stmt->bindValue(":idEvento", $idEvento, PDO::PARAM_INT);
            $stmt->execute();
            $ris = $stmt->fetch(PDO::FETCH_ASSOC);

            $postiDisponibili = (int) $postiTotali
                - (int) $ris["NumIscritti"] - (
                $costoEvento > 0 ? (int) $ris["NumPending"] : 0
            );

            return $postiDisponibili > 0 ? $postiDisponibili : 0;
        } catch (PDOException $e) {
            return ["msg" => "getPostiDisponibili" . $e->getMessage()];
        }
    }

    public static function alreadySubscribed($data)
    {
        if (
            !hasKeys($data, ["Nome", "Cognome", "IdEvento", "Cellulare"])
        ) {
            return ["msg" => "Parametri non validi!"];
        }
        try {
            $stmt = self::getConn()->prepare("SELECT MetodoPagamento FROM Iscrizioni 
        WHERE IdEvento = :idEvento AND Nome = :nome AND Cognome = :cognome AND Cellulare = :cellulare");

            $stmt->bindValue(":idEvento", $data["IdEvento"]);
            $stmt->bindValue(":nome", strtoupper($data["Nome"]), PDO::PARAM_STR);
            $stmt->bindValue(":cognome", strtoupper($data["Cognome"]), PDO::PARAM_STR);
            $stmt->bindValue(":cellulare", $data["Cellulare"], PDO::PARAM_STR);
            $stmt->execute();

            $metodoPagamento = $stmt->fetchColumn();

            return $metodoPagamento !== false ? $metodoPagamento : false;
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }
}
