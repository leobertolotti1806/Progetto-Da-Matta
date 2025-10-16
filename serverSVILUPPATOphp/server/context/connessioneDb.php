<?php
class ConnessioneDb
{
   protected static $conn;

   public static function getConn()
   {
      #La connessione è già stata inizializzata?
      if (self::$conn === null) {
         $host = "sql100.infinityfree.com";
         $dbname = "if0_40185200_vineria";

         try {
            self::$conn = new PDO(
               "mysql:host=$host;dbname=$dbname",
               "if0_40185200",
               "XvhYE75i2j0hYr"
            );

            self::$conn->exec("SET NAMES utf8mb4");
            self::$conn->exec("SET CHARACTER SET utf8mb4");

            /* $host = "sql104.infinityfree.com";
         $dbname = "if0_39460279_vineria";

         try {
            self::$conn = new PDO(
               "mysql:host=$host;dbname=$dbname",
               "if0_39460279",
               "leomix06"
            ); */

            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

         } catch (PDOException $exception) {
            throw new Exception("CONNESSIONE AL DB NON RIUSCITA: " . $exception->getMessage());
         }
      }

      #Se esiste già la connessione la ritorna
      return self::$conn;
   }
}



/*
function SCALAR(PDO $conn, $query, $params = [])
{
   $stmt = $conn->prepare($query);

   // Bind dei parametri
   foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
   }

   $stmt->execute();

   // Ritorna il primo valore della prima riga
   return $stmt->fetchColumn();
}

function NONQUERY(PDO $conn, $query, $params = [])
{
   $stmt = $conn->prepare($query);

   // Bind dei parametri
   foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
   }

   // Ritorna true se la query è andata a buon fine, false altrimenti
   return $stmt->execute();
}

function QUERY(PDO $conn, $query, $params = [])
{
   $stmt = $conn->prepare($query);

   // Bind dei parametri
   foreach ($params as $key => $value) {
      $stmt->bindValue($key, $value);
   }

   $stmt->execute();

   // Ritorna tutte le righe come array associativo
   return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
*/