<?php
namespace Ing2Toshl;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class ToshlClient
{
    public $client;
    public $readOnly;
    public $log;

    public function __construct($token, $readOnly, LoggerInterface $log)
    {
        $this->token = $token;
        $this->readOnly = $readOnly;
        $this->log = $log;

        $this->client = new Client(
            [
                // Base URI is used with relative requests
                'base_uri' => 'https://api.toshl.com',
                'auth' => [$token, ''],
                // You can set any number of default request options.
                'timeout' => 5.0,
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
     * @param string $extraKey
     * @param string $extraValue
     * @return Collection|IngTransaction[]
     */
    public function findTransactions(Carbon $date, $extraKey, $extraValue)
    {
        return $this->getTransactionsForDate($date)->filter(
            function (array $toshlTransaction) use ($extraKey, $extraValue) {
                $this->log->debug(
                    'Trying to match',
                    [
                        'key' => $extraKey,
                        'value' => $extraValue,
                        'extra' => empty($toshlTransaction['extra']) ? [] : $toshlTransaction['extra']
                    ]
                );

                if (!isset($toshlTransaction['extra'][$extraKey])) {
                    return false;
                }

                if ($extraValue !== $toshlTransaction['extra'][$extraKey]) {
                    return false;
                }

                return true;
            }
        );
    }

    public function addTransaction($accountId, Carbon $date, $amount, $comment, array $hashes)
    {
        $json = [
            'amount' => $amount,
            'currency' => ['code' => 'EUR'],
            'date' => $date->format('Y-m-d'),
            'account' => $accountId,
            'desc' => $comment,
            'extra' => $hashes,
        ];

        if ($this->readOnly) {
            $this->log->info('Read-only mode, skipping creation', $json);
        } else {
            $this->client->request('POST', "/entries", ['json' => $json]);
            $this->log->info('Transaction created successfully', $json);
        }
    }

    public function updateTransactionHashes(array $transaction, array $hashes)
    {
        $json = $transaction;

        $extra = empty($json['extra']) ? [] : $json['extra'];
        $json['extra'] = array_merge($extra, $hashes);

        $this->log->debug('Updating transaction', $json);

        if ($this->readOnly) {
            $this->log->info('Read-only mode, skipping update');
        } else {
            $this->client->request('PUT', "/entries/" . $transaction['id'], ['json' => $json]);
            $this->log->debug('Transaction updated successfully');
        }
    }
}