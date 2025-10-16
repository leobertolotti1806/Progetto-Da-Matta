<?php
class File
{
    /* private static $accounts = [];
    private static $clients = [];
    private static $services = [];

    private function init()
    {
        if (empty(self::$accounts) || empty(self::$services)) {

            self::$accounts = json_decode(file_get_contents(rtrim($_SERVER["DOCUMENT_ROOT"], "/") . "/secure/drive.key"), true);

            foreach (self::$accounts as $account) {
                $client = new Google_Client();
                $client->setClientId($account["clientId"]);
                $client->setClientSecret($account["clientSecret"]);
                $client->setAccessType("offline");
                $client->setScopes(["https://www.googleapis.com/auth/drive.file"]);

                $token = $client->fetchAccessTokenWithRefreshToken($account["refreshToken"]);

                if (isset($token["error"])) {
                    continue;
                }

                $service = new Google_Service_Drive($client);

                self::$clients[] = $client;
                self::$services[] = $service;
            }
        }
    }

    public function aggiungi($file)
    {
        self::init();

        $myFile = [
            "name" => $file["name"],
            "tmp_name" => $file["tmp_name"],
            "mimeType" => mime_content_type($file["tmp_name"])
        ];

        foreach (self::$accounts as $i => $account) {
            $fileId = self::tryUploadWithAccount($i, $myFile, $account["folderId"]);

            if ($fileId !== false) {
                return $fileId;
            }
        }

        return false;
    }

    private function tryUploadWithAccount($index, $file, $folderId)
    {
        $service = self::$services[$index];

        $fileMetadata = new Google_Service_Drive_DriveFile([
            "name" => $file["name"],
            "parents" => [$folderId]
        ]);

        try {
            $content = file_get_contents($file["tmp_name"]);
            $uploadedFile = $service->files->create($fileMetadata, [
                "data" => $content,
                "mimeType" => $file["mimeType"],
                "uploadType" => "multipart",
                "fields" => "id"
            ]);

            return $uploadedFile->id;
        } catch (Exception $e) {
            return false;
        }
    }

    public function elimina($fileId)
    {
        self::init();

        foreach (self::$services as $service) {
            try {
                $service->files->delete($fileId);
                return true;
            } catch (Exception $e) {
                continue;
            }
        }

        return false;
    } */

    public static function aggiungi($folder, $file, $fileName): bool
    {
        $tempPath = $file["tmp_name"];
        $originalType = mime_content_type($tempPath);
        $destination = rtrim($_SERVER["DOCUMENT_ROOT"], '/') . "/img/$folder/$fileName.webp";
        // Prova con GD (per i formati più comuni)

        if ($originalType === "image/webp") {
            return move_uploaded_file($tempPath, $destination);
        }

        $image = null;
        switch ($originalType) {
            case "image/jpeg":
            case "image/jpg":
                $image = @imagecreatefromjpeg($tempPath);
                break;

            case "image/png":
                $image = @imagecreatefrompng($tempPath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;

            case "image/gif":
                $image = @imagecreatefromgif($tempPath);
                break;

            case "image/bmp":
                $image = @imagecreatefrombmp($tempPath);
                break;

            case "image/avif":
                if (function_exists("imagecreatefromavif")) {
                    $image = @imagecreatefromavif($tempPath);
                }
                break;
        }

        // Se GD ha funzionato → salviamo
        if ($image) {
            $success = imagewebp($image, $destination, 85);
            imagedestroy($image);
            return $success;
        }

        // Fallback: Imagick
        try {
            $imagick = new Imagick($tempPath);
            $imagick->setImageFormat("webp");
            $imagick->setImageCompressionQuality(85);
            $imagick->writeImage($destination);
            $imagick->clear();
            $imagick->destroy();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function elimina($folder, $fileName): bool
    {
        $path = rtrim($_SERVER["DOCUMENT_ROOT"], '/') . "/img/$folder/$fileName.webp";
        return file_exists($path) ? unlink($path) : false;
    }
}