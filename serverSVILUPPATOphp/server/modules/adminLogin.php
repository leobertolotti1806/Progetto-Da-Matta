<?php
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data["email"], $data["pwd"])) {
    #LOGIN NORMALE
    if ($data["email"] == "marketbusca@gmail.com" && $data["pwd"] == "Password1!") {
        $obj["ok"] = true;
        require_once "./server/context/token.php";

        setToken("adminAuth", [
            "email" => "marketbusca@gmail.com",
            "pwd" => "Password1!"
        ], 600000);
    } else {
        $obj["msg"] = "Credenziali sbagliate!";
    }
} else {
    $obj["msg"] = "Parametri non validi!";
}

header("Content-Type: application/json");
echo json_encode($obj);