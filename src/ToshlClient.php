<?php
namespace Ing2Toshl;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class ToshlClient
{
    public $client;
    public $log;

    public function __construct($token, LoggerInterface $log)
    {
        $this->token = $token;
        $this->log = $log;

        $this->client = new Client(
            [
                // Base URI is used with relative requests
                'base_uri' => 'https://api.toshl.com',
                'auth' => [$token, ''],
                // You can set any number of default request options.
                'timeout' => 2.0,
            ]
        );
    }

    public function findAccountId($name)
    {
        // find accountId
        $res = $this->client->request('GET', "accounts");
        $accounts = new \Illuminate\Support\Collection(json_decode($res->getBody(), true));
        $accountId = (int)$accounts->filter(
            function (array $item) use ($name) {
                return $item['name'] === $name;
            }
        )->first()['id'];

        if (empty($accountId)) {
            throw new \Exception('AccountId not found');
        }

        return $accountId;
    }

    public function getTransactionsForDate(Carbon $date)
    {
        $this->log->info('Retieving transactions for', ['date' => $date->format('Y-m-d')]);

        $from = $date->format('Y-m-d');
        $to = $date->format('Y-m-d');
        $res = $this->client->request('GET', "entries?page=0&expand=true&from=$from&to=$to");

        $toshlTransactions = new \Illuminate\Support\Collection(json_decode($res->getBody(), true));

        $this->log->info('Transactions loaded', $toshlTransactions->toArray());

        return $toshlTransactions;
    }

    /**
     * @param Carbon $date
     * @param string $firstLinesHash
     * @param string $commentStartHash
     * @return Collection|IngTransaction[]
     */
    public function findTransactions(Carbon $date, $firstLinesHash, $commentStartHash)
    {
        $toshlTransactions = $this->getTransactionsForDate($date);

        return $toshlTransactions->filter(
            function (array $toshlTransaction) use ($firstLinesHash, $commentStartHash) {
                $this->log->info('Trying to match', ['transaction' => $toshlTransaction]);

                if (!empty($toshlTransaction['extra']['firstLinesHash'])
                    && $firstLinesHash === $toshlTransaction['extra']['firstLinesHash']
                ) {
                    $this->log->info('firstLinesHash match');
                    return true;
                }

                if (!empty($toshlTransaction['extra']['commentStartHash'])
                    && $commentStartHash === $toshlTransaction['extra']['commentStartHash']
                ) {
                    $this->log->info('commentStartHash match');
                    return true;
                }

                $this->log->info('No match');
                return false;
            }
        );
    }

    public function addTransaction($accountId, Carbon $date, $amount, $comment, $firstLinesHash, $commentStartHash)
    {
        $json = [
            'amount' => $amount,
            'currency' => ['code' => 'EUR'],
            'date' => $date->format('Y-m-d'),
            'account' => $accountId,
            'desc' => $comment,
            'extra' => ['firstLinesHash' => $firstLinesHash, 'commentStartHash' => $commentStartHash],
        ];
        $this->client->request('POST', "/entries", ['json' => $json]);
        $this->log->info('Transaction created successfully', $json);
    }
}