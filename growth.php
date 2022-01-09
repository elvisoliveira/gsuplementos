<?php
require "vendor/autoload.php";

$dom = new PHPHtmlParser\Dom;
$dom->loadFromUrl("https://www.gsuplementos.com.br/whey-protein-concentrado-1kg-growth-supplements-p985936");

$feedback = [];

$list = $dom->find("div.barraFlutuante")->find("option");
foreach($list as $i) {
    if(preg_match("/(Natural|Beijinho|Brigadeiro)/i", $i->innerHtml) && strpos($i->innerHtml, "Indisponível") == false) {
        array_push($feedback, $i->innerHtml);
    }
}

foreach(["port", "group", "server"] as $item) {
    ${$item} = getenv(strtoupper($item));
}

if(count($feedback) > 0) {
    $previous = json_decode(file_get_contents("growth.json"), true);
    if($feedback != $previous) {
        file_put_contents("growth.json", json_encode($feedback));
        $client = new GuzzleHttp\Client();
        $client->postAsync("http://{$server}:{$port}/sendText", [
            "json" => [
                "args" => [
                    "to" => $group,
                    "content" => implode(PHP_EOL, $feedback)
                ]
            ]
        ])->then(function($result) {
            return true;
        })->wait(false);
    }
}
