<?php

namespace App;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;


class Api
{
    private $client;
    private string $apiKey = "c899ss12-bt2d-834c-k4a1-34923d6721g6";


    public function __construct()
    {
        $this->client = new Client();
    }

    private function generateQuery(string $request, string $userInput = null): array
    {
        if ($request === 'listings') {
            return [
                "start" => "1",
                "limit" => "10",
            ];
        } else {
            return [
                "symbol" => $userInput
            ];
        }
    }

    public function makeRequest(string $request, string $userInput = null): string
    {
        $apiUrl = "https://pro-api.coinmarketcap.com/v1/cryptocurrency/$request/latest";

        try {
            $response = $this->client->request('GET', $apiUrl, [
                "headers" => [
                    "X-CMC_PRO_API_KEY" => $this->apiKey,
                ],
                "query" => $this->generateQuery($request, $userInput)
            ]);

            return $response->getBody();

        } catch (ClientException $e) {
            return "Client error: " . $e->getMessage();
        } catch (ServerException $e) {
            return "Server error: " . $e->getMessage();
        } catch (RequestException $e) {
            return "Request error: " . $e->getMessage();
        } catch (Exception $e) {
            return "General error: " . $e->getMessage();
        }
    }
}

