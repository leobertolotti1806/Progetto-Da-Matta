<?php

require_once "./server/libs/satispay/init.php";

$obj = getAcceptedPaymentsOfDay($data["date"]);

if (!isset($obj["msg"])) {
    if (!empty($obj["payments"])) {

        $notAccepted = [];

        require_once "./server/controllers/pagamenti.php";

        foreach ($obj["eventIds"] as $idEvento) {
            $ris = Pagamento::validaPagamentiRegistrati($idEvento);

            if (hasKeys($ris, ["ok", "paymentData"]) && !empty($ris["paymentData"])) {
                $notAccepted = array_merge($notAccepted, $ris["paymentData"]);

                foreach ($ris["paymentData"] as $p) {
                    if (!in_array($p["Evento"], $obj["eventIds"])) {
                        $obj["eventIds"][] = $p["Evento"];
                    }
                }
            }
        }

        require_once "./server/controllers/eventi.php";
        
        $titoli = Evento::getTitoliByIds($obj["eventIds"]);

        if (!isset($titoli["msg"])) {

            $obj["payments"] = array_merge($notAccepted, $obj["payments"]);

            usort($obj["payments"], function ($a, $b) {

                $timeA = strtotime($a["Data"]);
                $timeB = strtotime($b["Data"]);

                // Ordina decrescente (dal più recente al più vecchio)
                return $timeB <=> $timeA;
            });

            for ($i = 0; $i < count($obj["payments"]); $i++) {
                $obj["payments"][$i]["Evento"] = $titoli[$obj["payments"][$i]["Evento"]];
            }

            require_once "./server/controllers/pagamenti.php";

            $obj = [
                "ok" => true,
                "payments" => $obj["payments"]
            ];
        } else {
            $obj = ["msg" => $titoli["msg"]];
        }
    } else {
        $obj = [
            "ok" => true,
            "payments" => $obj["payments"]
        ];
    }
}

header("Content-Type: application/json");
echo json_encode($obj);
die;

function getAcceptedPaymentsOfDay(string $date): array
{
    $tz = new DateTimeZone("Europe/Rome");

    // Limiti del giorno convertiti in UTC
    $startUtc = (new DateTimeImmutable("$date 00:00:00", $tz))
        ->setTimezone(new DateTimeZone("UTC"))
        ->getTimestamp();
    $endUtc = (new DateTimeImmutable("$date 23:59:59", $tz))
        ->setTimezone(new DateTimeZone("UTC"))
        ->getTimestamp();

    $params = ["limit" => 100, "status" => "ACCEPTED"];

    $allPayments = [];
    $eventIds = [];
    $startingAfter = null;
    $prevFirstId = null;
    $maxPages = 1000;
    $page = 0;

    do {
        if ($startingAfter) {
            $params["starting_after"] = $startingAfter;
        } else {
            unset($params["starting_after"]);
        }

        $resp = \SatispayGBusiness\Payment::all($params);
        if (empty($resp->data))
            break;

        // Protezione: se la pagina si ripete → fermati
        $firstId = $resp->data[0]->id ?? null;
        if ($firstId === $prevFirstId)
            break;
        $prevFirstId = $firstId;

        foreach ($resp->data as $p) {
            if (empty($p->insert_date))
                continue;

            $ts = strtotime($p->insert_date);
            if (
                $ts >= $startUtc && $ts <= $endUtc && !empty($p->metadata)
                && hasKeys((array) $p->metadata, ["Cellulare", "IdEvento", "Nome", "Cognome", "Origin"])
                && $p->metadata->Origin == "MyApplicationDaMatta"
            ) {
                $allPayments[] = [
                    "Data" => $p->insert_date,
                    "Importo" => number_format($p->amount_unit / 100, 2, ',', '.'),
                    "Iscritto" => $p->metadata->Cognome . " " . $p->metadata->Nome,
                    "Evento" => $p->metadata->IdEvento,
                    "Cellulare" => $p->metadata->Cellulare,
                    "Tipo" => $p->type === "TO_BUSINESS" ? "E" : "R",
                    "Status" => $p->status
                ];

                if (!in_array($p->metadata->IdEvento, $eventIds)) {
                    $eventIds[] = $p->metadata->IdEvento;
                }
            }

            // Se siamo già oltre il giorno richiesto → esci
            if ($ts < $startUtc)
                return [
                    "payments" => $allPayments,
                    "eventIds" => $eventIds
                ];
        }

        $last = end($resp->data);
        $startingAfter = $last->id;

        $page++;
        if ($page > $maxPages) {
            return ["msg" => "Troppe pagine, possibile loop infinito (errore 69831)"];
        }
    } while (count($resp->data) === $params["limit"]);

    return [
        "payments" => $allPayments,
        "eventIds" => $eventIds
    ];
}