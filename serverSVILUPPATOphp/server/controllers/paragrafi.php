<?php
require_once "./server/context/connessioneDb.php";

class Paragrafo extends ConnessioneDb
{
    public static function modifica($data)
    {
        if (
            !isset($data[33]) && !isset($data["Id"], $data["testo"])
        ) {
            return ["msg" => "Parametri non validi!"];
        }

        if (isset($data[33])) {
            unset($data["Id"]);
            foreach ($data as $id => $testo) {
                try {
                    $stmt = self::getConn()->prepare("UPDATE Paragrafi SET Testo = :testo WHERE Id = :id");
                    $stmt->bindValue(":testo", $testo);
                    $stmt->bindValue(":id", $id, PDO::PARAM_INT);
                    $stmt->execute();
                } catch (PDOException $e) {
                    return ["msg" => $e->getMessage()];
                }
            }

            return true;
        } else {
            try {
                $stmt = self::getConn()->prepare("UPDATE Paragrafi SET Testo = :testo WHERE Id = :id");
                $stmt->bindValue(":testo", $data["testo"]);
                $stmt->bindValue(":id", $data["Id"], PDO::PARAM_INT);
                return $stmt->execute();
            } catch (PDOException $e) {
                return ["msg" => $e->getMessage()];
            }
        }
    }


    public static function getAllbyPage($pagina)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT Id, Testo FROM Paragrafi WHERE Pagina = :pagina");
            $stmt->bindValue(":pagina", $pagina);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }

    public static function getById($id)
    {
        try {
            $stmt = self::getConn()->prepare("SELECT Testo FROM Paragrafi WHERE Id = :id");
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ["msg" => $e->getMessage()];
        }
    }
}