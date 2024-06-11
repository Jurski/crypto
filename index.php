<?php

require 'vendor/autoload.php';

use App\App;


$app = new App();

$userOptions = [
    "1" => "List top crypto's",
    "2" => "Search by symbol",
    "3" => "Purchase crypto",
    "4" => "Sell crypto",
    "5" => "Show wallet state",
    "6" => "Show transaction history",
    "7" => "Exit",
];


while (true) {
    echo "Options:" . PHP_EOL;
    foreach ($userOptions as $option => $value) {
        echo "- $option: $value" . PHP_EOL;
    }

    $inputOption = trim(readline("Enter what you want to do: "));

    switch ($inputOption) {
        case "1":
            echo $app->listTopCryptos() ?? "No Cryptos found";
            break;
        case "2":
            $userInput = trim(readline("enter symbol what to search: "));

            if ($userInput === "") {
                echo "Non empty input required!" . PHP_EOL;
                break;
            }

            echo $app->listSingleCrypto($userInput) ?? "Couldnt find cryptocurrency: " . $userInput . PHP_EOL;
            break;
        case "3":
            $symbol = trim(strtoupper(readline("enter symbol to buy: ")));
            $amount = trim(readline("enter amount to buy: "));

            if ($symbol === "" || $amount === "") {
                echo "Non empty input required!" . PHP_EOL;
                break;
            }

            if (!is_numeric($amount)) {
                echo "Number value required!";
                break;
            }

            $app->buyCrypto($symbol, $amount);
            break;
        case "4":
            $symbol = trim(strtoupper(readline("enter symbol to sell: ")));
            $amount = trim(readline("enter amount to sell: "));

            if ($symbol === "" || $amount === "") {
                echo "Non empty input required!" . PHP_EOL;
                break;
            }

            if (!is_numeric($amount)) {
                echo "Number value required!";
                break;
            }

            $app->sellCrypto($symbol, $amount);
            break;
        case "5":
            $app->displayWalletState();
            break;
        case "6":
            $app->displayTransactions();
            break;
        case "7":
            exit("Bye!");
        default:
            echo "Undefined option!" . PHP_EOL;
            break;
    }
}