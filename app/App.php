<?php

namespace App;

use InitPHP\CLITable\Table;

class App
{
    private Api $api;
    private Wallet $wallet;

    public function __construct()
    {
        $this->api = new Api();
        $this->wallet = new Wallet();
    }

    private function fillTable(array $apiData): ?Table
    {
        if (isset($apiData['data']) && count($apiData['data']) > 0) {
            $table = new Table();
            foreach ($apiData["data"] as $cryptocurrency) {
                $table->row([
                    'id' => $cryptocurrency["id"],
                    'name' => $cryptocurrency["name"],
                    'symbol' => $cryptocurrency["symbol"],
                    'price' => number_format($cryptocurrency["quote"]["USD"]["price"], 2) . " $",
                ]);
            }

            return $table;
        }
        return null;
    }

    public function listTopCryptos(): ?Table
    {
        $response = $this->api->makeRequest('listings');
        $apiData = json_decode($response, true);

        return $this->fillTable($apiData);
    }

    public function listSingleCrypto(string $userInput): ?Table
    {
        $response = $this->api->makeRequest('quotes', $userInput);
        $apiData = json_decode($response, true);

        return $this->fillTable($apiData);
    }

    public function buyCrypto(string $symbol, float $amount): void
    {
        $response = $this->api->makeRequest('quotes', $symbol);
        $apiData = json_decode($response);


        if (empty($apiData->data->$symbol)) {
            echo "No such crypto symbol $symbol found" . PHP_EOL;
            return;
        }

        $name = $apiData->data->$symbol->name;
        $symbol = $apiData->data->$symbol->symbol;
        $price = $apiData->data->$symbol->quote->USD->price;

        $cryptocurrency = new Cryptocurrency($name, $symbol, $price);

        $this->wallet->buyCrypto($cryptocurrency, $amount);

        file_put_contents("data/wallet.json", json_encode($this->wallet, JSON_PRETTY_PRINT));
    }

    public function sellCrypto(string $symbol, float $amount): void
    {
        $response = $this->api->makeRequest('quotes', $symbol);
        $apiData = json_decode($response);

        $name = $apiData->data->$symbol->name;
        $symbol = $apiData->data->$symbol->symbol;
        $price = $apiData->data->$symbol->quote->USD->price;

        $cryptocurrency = new Cryptocurrency($name, $symbol, $price);

        $this->wallet->sellCrypto($cryptocurrency, $amount);

        file_put_contents("data/wallet.json", json_encode($this->wallet, JSON_PRETTY_PRINT));
    }

    public function displayWalletState(): void
    {
        $cash = $this->wallet->getBalanceUsd();
        $cashFormatted = number_format($cash, 2);

        echo "Cash balance - " . $cashFormatted . "$" . PHP_EOL;

        $holdings = $this->wallet->getHoldings();

        if (empty($holdings)) {
            echo "No holdings to display." . PHP_EOL;
            return;
        }

        $currentValues = [];
        $table = new Table();

        foreach ($holdings as $symbol => $amount) {
            $response = $this->api->makeRequest('quotes', $symbol);
            $apiData = json_decode($response);
            $price = $apiData->data->$symbol->quote->USD->price;

            $value = $price * $amount;

            $currentValues[$symbol] = $value;

            $table->row([
                'name' => $symbol,
                'amount' => $amount,
                'value' => number_format($value, 2) . " $",
            ]);
        }

        $holdingsSum = array_sum($currentValues);

        $totalBalance = $cash + $holdingsSum;
        $totalBalanceFormatted = number_format($totalBalance, 2);

        echo "Total balance - " . $totalBalanceFormatted . "$" . PHP_EOL;

        echo $table;
    }

    public function displayTransactions(): void
    {
        $transactions = $this->wallet->getTransactions();

        $table = new Table();

        foreach ($transactions as $transaction) {
            $formattedDate = $transaction->getDate()->setTimezone('Europe/Riga')->format('d-m-Y H:i:s');

            $table->row([
                'date' => $formattedDate,
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'cryptocurrency' => $transaction->getCryptocurrency(),
                'price' => number_format($transaction->getPurchasePrice(), 2) . " $",
            ]);
        }

        echo $table;
    }
}