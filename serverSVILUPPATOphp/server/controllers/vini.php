<?php
require_once "./server/context/connessioneDb.php";
class Vino extends ConnessioneDb
{
    public static function aggiungi($data)
    {
        if (
            !hasKeys($data, ["Marca", "Nome", "Anno", "Costo", "Quantita", "Effervescenza", "Colore"])
            || !isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK
        ) {
            return ["msg" => "Parametri non validi!"];
        }

        if (strpos(mime_content_type($_FILES["file"]["tmp_name"]), "image/") !== 0) {
            return ["msg" => "Formato immagine non valido!"];
        }

        /* require_once "./server/controllers/file.php";

        $data["ImageId"] = (new File())->aggiungi($_FILES["file"]);

        if (!$data["ImageId"]) {
            return ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo. Riprova"];
        } */

        if (!isset($data["Offerta"]) || $data["Offerta"] == "" || $data["Offerta"] == "null") {
            $data["Offerta"] = null;
        }

        if (!isset($data["Regione"]) || $data["Regione"] == "" || $data["Regione"] == "null") {
            $data["Regione"] = null;
        }

        if (!isset($data["Gradazione"]) || $data["Gradazione"] == "" || $data["Gradazione"] == "null") {
            $data["Gradazione"] = null;
        }

        if (!isset($data["Denominazione"]) || $data["Denominazione"] == "" || $data["Denominazione"] == "null") {
            $data["Denominazione"] = null;
        }

        foreach ($data as $key => $value) {
            $addClause[] = ":$key";
            $fieldCluases[] = "$key";
        }

        try {
            $stmt = self::getConn()->prepare(
                "INSERT INTO Vini (" . implode(",", $fieldCluases) . ")
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

        require_once "./server/controllers/file.php";

        return File::aggiungi("vini", $_FILES["file"], $newId)
            ? true :
            ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo. Riprova"];
    }

    public static function modifica($data)
    {
        if (!hasKeys($data, ["Id", "Marca", "Nome", "Descrizione", "Anno", "Costo", "Quantita", "Effervescenza"])) {
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

        if (!isset($data["Offerta"]) || $data["Offerta"] == "" || $data["Offerta"] == "null") {
            $data["Offerta"] = null;
        }

        if (!isset($data["Regione"]) || $data["Regione"] == "" || $data["Regione"] == "null") {
            $data["Regione"] = null;
        }

        if (!isset($data["Denominazione"]) || $data["Denominazione"] == "" || $data["Denominazione"] == "null") {
            $data["Denominazione"] = null;
        }

        if (!isset($data["Gradazione"]) || $data["Gradazione"] == "" || $data["Gradazione"] == "null") {
            $data["Gradazione"] = null;
        }

        $setClause = [];
        $id = $data["Id"];
        unset($data["Id"]);

        foreach ($data as $key => $value) {
            $setClause[] = "$key = :$key";
        }

        try {
            $stmt = self::getConn()->prepare("UPDATE Vini SET " . implode(", ", $setClause) . " WHERE Id = :Id");

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->bindValue(":Id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }

        if ($changeImage) {
            if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
                return ["msg" => "Nessun file caricato!"];
            }

            #MODIFICA DEL FILE SU DRIVE
            /* require_once "./server/controllers/file.php";

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

            if (!File::aggiungi("vini", $_FILES["file"], $id)) {
                return ["msg" => "Mi dispiace ma il caricamento dell'immagine non è avvenuto con successo"];
            }
        }
    }

    public static function elimina($id)
    {
        try {
            $stmt = self::getConn()->prepare("DELETE FROM Vini WHERE Id = :id");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }

        require_once "./server/controllers/file.php";

        return File::elimina("vini", $id) ? true : ["msg" => "Mi dispiace ma l'eliminazione dell'immagine non è andata a buon fine.\nUna soluzione potrebbe essere quella di eliminarla manualmente."];
    }

    public static function getById($id, $minimal = false)
    {
        $sql = "SELECT ";

        if ($minimal) {
            $sql .= "Nome, Costo FROM Vini";
        } else {
            $sql .= "* FROM Vini";
        }

        $sql .= " WHERE Id = :id";

        try {
            $stmt = self::getConn()->prepare($sql);

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();


            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getAdminVini($data)
    {
        $sortBy = $data["sortBy"] == "" ? "Nome" : $data["sortBy"];

        if ($sortBy == "Costo") {
            $sortBy = "COALESCE(Offerta, Costo)";
        }

        $sortDir = $data["sortDir"] == "" ? "DESC" : $data["sortDir"];
        $limit = intval($data["limit"]);
        $offset = intval($data["offset"]);

        $sql = "SELECT Id, Nome, Anno, 
        CASE WHEN Offerta IS NOT NULL THEN CONCAT(Costo, '-', Offerta)
        ELSE Costo END AS Costo, Evidenzia, Colore,
         Marca, Regione, 
        CASE WHEN Offerta IS NULL THEN 0 ELSE 1 END AS Offerta 
        FROM Vini ";

        if ($data["search"] != "") {
            $sql .= " WHERE Nome LIKE :search OR 
                Anno LIKE :search OR 
                Costo LIKE :search OR 
                Colore LIKE :search OR 
                Effervescenza LIKE :search OR 
                Marca LIKE :search OR 
                Regione LIKE :search";
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

    public static function getViniEvidenziati()
    {
        try {
            $stmt = self::getConn()->prepare("SELECT Id, Nome, Colore, Anno, Costo, Offerta, Quantita, Marca 
            FROM Vini WHERE Evidenzia = 1");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getVini($data)
    {
        if (
            !in_array($data["sortBy"], [
                "Anno",
                "Costo",
                "Quantita",
                "Gradazione",
                "Offerta",
                "Effervescenza",
                "Colore",
                "Marca",
                "Nome",
                "Denominazione"
            ])
        ) {
            $data["sortBy"] = "Nome";
        }

        if ($data["order"] != "ASC" && $data["order"] != "DESC") {
            $data["order"] = "ASC";
        }

        $sql = "SELECT Id, Nome, Anno, Costo, Offerta, Quantita, Colore, Marca, Evidenzia
             FROM Vini WHERE ";

        if (isset($data["getAll"])) {
            $sql .= "1=1 ";
        } else if ($data["firstTime"]) {
            $sql .= "Evidenzia = 0 ";
        } else {
            $sql .= "1=1 ";
        }

        $params = [];

        if (!empty($data["effervescenza"]) && $data["effervescenza"] != "Tutti") {
            $sql .= "AND Effervescenza = :effervescenza ";
            $params[':effervescenza'] = $data["effervescenza"];
        }

        if (!empty($data["colore"]) && $data["colore"] != "Tutti") {
            $sql .= "AND Colore = :colore ";
            $params[':colore'] = $data["colore"];
        }

        if (!empty($data["Marca"])) {
            $sql .= "AND Marca LIKE :Marca ";
            $params[':Marca'] = $data["Marca"];
        }

        if (!empty($data["search"])) {
            $sql .= "AND (
            Nome LIKE :search OR Effervescenza LIKE :search 
            OR Marca LIKE :search OR Colore LIKE :search ";

            if (is_numeric($data["search"])) {
                $sql .= "OR Quantita = :search or Anno LIKE :search 
                OR Costo = :search OR Offerta LIKE :search OR Gradazione LIKE :search ";
            }

            $sql .= ") ";

            $params[':search'] = "%" . $data["search"] . "%";
        }

        if (!empty($data["sortBy"])) {
            if ($data["sortBy"] == "Costo") {
                $sql .= "ORDER BY COALESCE(Offerta, Costo) " . $data["order"];
            } else {
                $sql .= "ORDER BY " . $data["sortBy"] . " " . $data["order"];
            }
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        try {
            /* echo $sql; */
            $stmt = self::getConn()->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', (int) $data["limit"], PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $data["offset"], PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }
}
