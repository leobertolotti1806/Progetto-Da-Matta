<?php
require_once "./server/context/connessioneDb.php";

class Evento extends ConnessioneDb
{
    public static function aggiungi($data)
    {
        if (
            !hasKeys($data, ["Titolo", "Descrizione", "Data", "OraInizio", "OraFine", "PostiTotali", "Costo"])
            || !isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK
        ) {
            return ["msg" => "Parametri non validi!"];
        }

        if (strpos(mime_content_type($_FILES["file"]["tmp_name"]), "image/") !== 0) {
            return ["msg" => "Formato immagine non valido!"];
        }

        if (!isset($data["ScadenzaIscrizione"]) || $data["ScadenzaIscrizione"] == "" || $data["ScadenzaIscrizione"] == "null") {
            $data["ScadenzaIscrizione"] = $data["Data"];
        } else if ($data["ScadenzaIscrizione"] > $data["Data"]) {
            return ["msg" => "Non puoi mettere una scadenza iscrizioni dopo la data dell'evento!"];
        }

        if ($data["Costo"] == "" || $data["Costo"] == "null") {
            $data["Costo"] = 0.00;
        }

        if (!isset($data["Indirizzo"]) || $data["Indirizzo"] == "" || $data["Indirizzo"] == "null") {
            $data["Indirizzo"] = 'Via Roberto d’azeglio 13, 12022 Busca';
        }

        if (!isset($data["Citta"]) || $data["Citta"] == "" || $data["Citta"] == "null") {
            $data["Citta"] = "Busca";
        }

        foreach ($data as $key => $value) {
            $addClause[] = ":$key";
            $fieldCluases[] = "$key";
        }

        try {
            $stmt = self::getConn()->prepare(
                "INSERT INTO Eventi (" . implode(",", $fieldCluases) . ")
            VALUES (" . implode(",", $addClause) . ")"
            );

            foreach ($data as $param => $value) {
                $stmt->bindValue(
                    ":$param",
                    $value,
                    is_string($value) ? PDO::PARAM_STR :
                    (is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_BOOL)
                );
            }

            $stmt->execute();
            $newId = self::getConn()->lastInsertId();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }

        /* require_once "./server/controllers/file.php";

        $data["ImageId"] = (new File())->aggiungi($_FILES["file"]);

        if (!$data["ImageId"]) {
            return ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo. Riprova"];
        } */

        require_once "./server/controllers/file.php";

        return (new File())->aggiungi("eventi", $_FILES["file"], $newId)
            ? true :
            ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo. Riprova"];
    }

    public static function modifica($data)
    {
        if (!hasKeys($data, ["Id", "Titolo", "Descrizione", "Data", "OraInizio", "OraFine", "PostiTotali", "Costo"])) {
            return ["msg" => "Parametri non validi"];
        }

        $changeImage = false;

        if (isset($data["changeImage"])) {
            unset($data["changeImage"]);
            $changeImage = true;

            if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
                return ["msg" => "Nessun file caricato!"];
            }

            if (strpos(mime_content_type($_FILES["file"]["tmp_name"]), "image/") !== 0) {
                return ["msg" => "Formato immagine non valido!"];
            }
        }

        if (!isset($data["ScadenzaIscrizione"]) || $data["ScadenzaIscrizione"] == "" || $data["ScadenzaIscrizione"] == "null") {
            $data["ScadenzaIscrizione"] = $data["Data"];
        } else if ($data["ScadenzaIscrizione"] > $data["Data"]) {
            return ["msg" => "Non puoi mettere una scadenza iscrizioni dopo la data dell'evento!"];
        }

        if ($data["Costo"] == "" || $data["Costo"] == "null") {
            $data["Costo"] = 0.00;
        }

        if (!isset($data["Indirizzo"]) || $data["Indirizzo"] == "" || $data["Indirizzo"] == "null") {
            $data["Indirizzo"] = 'Via Roberto d’azeglio 13, 12022 Busca';
        }

        if (!isset($data["Citta"]) || $data["Citta"] == "" || $data["Citta"] == "null") {
            $data["Citta"] = "Busca";
        }

        $setClause = [];
        $id = $data["Id"];
        unset($data["Id"]);

        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }

        try {
            $stmt = self::getConn()->prepare("UPDATE Eventi SET " . implode(", ", $setClause) . " WHERE Id = :Id");

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->bindValue(":Id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }

        if ($changeImage) {
            /*
            #MODIFICA FILE SU DRIVE
             require_once "./server/controllers/file.php";

            $file = new File();

            if ($file->elimina($data["ImageId"])) {
                $data["ImageId"] = $file->aggiungi($_FILES["file"]);

                if (!$data["ImageId"]) {
                    return [
                        "msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo"
                    ];
                }
            } else {
                return [
                    "msg" => "Mi dispiace ma non è stato possibile eliminare il file, prova manualmente al link\n 
                    https://drive.google.com/file/d/" . $data["ImageId"] . "/view?usp=drive_link"
                ];
            } */

            require_once "./server/controllers/file.php";

            if (!File::aggiungi("event", $_FILES["file"], $id)) {
                return ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo"];
            }
        }
    }

    public static function elimina($id)
    {
        require_once "./server/controllers/iscrizioni.php";

        $ris = Iscrizione::eliminaIscrittiEvento($id);

        if (isset($ris["msg"])) {
            return $ris;
        }

        try {
            $stmt = self::getConn()->prepare("DELETE FROM Eventi WHERE Id = :id");
            $stmt->bindValue(":id", $id);
            $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }

        require_once "./server/controllers/file.php";

        return File::elimina("event", $id) ? true : ["msg" => "Mi dispiace ma l'eliminazione dell'immagine non è andata a buon fine.\nUna soluzione potrebbe essere quella di eliminarla manualmente."];
    }

    public static function getById($idEvento, $minimal = false)
    {
        $sql = "SELECT ";

        if ($minimal) {
            $sql .= " Titolo, Costo, PostiTotali, ScadenzaIscrizione, Data ";
        } else {
            $sql .= " * ";
        }

        $sql .= "FROM Eventi e WHERE e.Id = :idEvento";

        try {
            $stmt = self::getConn()->prepare($sql);

            $stmt->bindValue(":idEvento", $idEvento, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getTitoliByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        try {
            $stmt = self::getConn()->prepare(
                "SELECT Id, Titolo, Data FROM Eventi WHERE Id IN (" . str_repeat('?,', count($ids) - 1) . "?)"
            );
            $stmt->execute($ids);

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$row['Id']] = [
                    'Titolo' => $row['Titolo'],
                    'Data' => $row['Data']
                ];
            }

            return $results;

        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }


    public static function getNonFreeEvent()
    {
        try {
            $stmt = self::getConn()->prepare("SELECT Id FROM Eventi WHERE Costo > 0");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }


    public static function getAdminEventi($data)
    {
        $allNonFreeEventIds = self::getNonFreeEvent();
        if (isset($allNonFreeEventIds["msg"])) {
            return $allNonFreeEventIds;
        }

        require_once "./server/controllers/pagamenti.php";

        foreach ($allNonFreeEventIds as $eventId) {
            $ris = Pagamento::validaPagamentiRegistrati($eventId);
            if (isset($ris["msg"])) {
                return $ris;
            }
        }

        $sortBy = $data["sortBy"] == "" ? "Data" : $data["sortBy"];
        $sortDir = $data["sortDir"] == "" ? "DESC" : $data["sortDir"];
        $limit = intval($data["limit"]);
        $offset = intval($data["offset"]);

        $sql = "SELECT Id, Titolo, Data, PostiTotali, Costo, ScadenzaIscrizione,
                (SELECT COUNT(*) FROM Iscrizioni i WHERE i.IdEvento = e.Id) AS Iscritti 
                FROM Eventi e ";

        if ($data["search"] != "") {
            $sql .= "WHERE e.Titolo LIKE :search OR
                e.Data LIKE :search OR
                e.ScadenzaIscrizione LIKE :search OR
                e.PostiTotali LIKE :search OR
                e.Costo LIKE :search";
        }
        #IL CONTROLLO PER EVITARE SQL INJECTION E' GIA' STATO FATTO NELL'adminApi.php
        $sql .= " ORDER BY $sortBy $sortDir LIMIT :limit OFFSET :offset";

        try {
            $stmt = self::getConn()->prepare($sql);

            if ($data["search"] != "") {
                $searchLike = "%" . $data["search"] . "%";
                $stmt->bindValue(":search", $searchLike, PDO::PARAM_STR);
            }

            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getEventi($data)
    {
        if (isset($data["basic"])) {
            try {
                $stmt = self::getConn()->prepare("SELECT Id, Titolo, Data, 
                LEFT(Descrizione, 180) AS Descrizione FROM Eventi 
                WHERE STR_TO_DATE(Data, '%Y-%m-%d') >= CURDATE() - INTERVAL 30 DAY
                 ORDER BY STR_TO_DATE(Data, '%Y-%m-%d') ASC");
                $stmt->execute();
                $ris = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return ["msg" => $e->getMessage()];
            }

            $today = date("Y-m-d");

            $eventi = ["next" => [], "past" => []];

            foreach ($ris as $e) {
                if ($e["Data"] >= $today) {
                    $eventi["next"][] = $e;
                } else {
                    $eventi["past"][] = $e;
                }
            }
        } else if (isset($data["limit"], $data["offset"])) {
            try {
                $stmt = self::getConn()->prepare("SELECT Id, Titolo, Data, 
                LEFT(Descrizione, 180) AS Descrizione FROM Eventi 
                WHERE STR_TO_DATE(Data, '%Y-%m-%d') < CURDATE() - INTERVAL 30 DAY 
                ORDER BY STR_TO_DATE(Data, '%Y-%m-%d') DESC LIMIT :limit OFFSET :offset");

                $stmt->bindValue(":limit", $data["limit"], PDO::PARAM_INT);
                $stmt->bindValue(":offset", $data["offset"], PDO::PARAM_INT);

                $stmt->execute();
                $eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                return ["msg" => $e->getMessage()];
            }
        } else {
            $eventi = ["msg" => "Parametri non validi"];
        }

        return $eventi;
    }
}
