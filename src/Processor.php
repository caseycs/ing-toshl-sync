<?php
namespace Ing2Toshl;

use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class Processor
{
    public function addMissingTransactionToToshl(
        ToshlClient $toshlClient,
        $toshAccountName,
        Collection $ingTransactions,
        LoggerInterface $log
    ) {
        $accountId = $toshlClient->findAccountId($toshAccountName);
        $transactionsCreated = 0;

        /** @var IngTransaction[] $ingTransactions */
        foreach ($ingTransactions as $ingTransaction) {
            $log->info('Trying to match', get_object_vars($ingTransaction));

            $toshTransactions = $toshlClient->findTransactions(
                $ingTransaction->date,
                $ingTransaction->firstLinesHash(),
                $ingTransaction->commentStartHash()
            );

            if ($toshTransactions->count() > 1) {
                $log->critical(
                    'Multiple matching transactions found, skipping',
                    get_object_vars($ingTransaction)
                );
                continue;
            } elseif ($toshTransactions->count() === 1) {
                $log->info('transaction already exists', get_object_vars($ingTransaction));
                continue;
            }

            $toshlClient->addTransaction(
                $accountId,
                $ingTransaction->date,
                $ingTransaction->amount,
                $ingTransaction->comment,
                $ingTransaction->firstLinesHash(),
                $ingTransaction->commentStartHash()
            );

            $transactionsCreated ++;
        }

        $log->info('All transactions synced', ['transactionsCreated' => $transactionsCreated]);

        return $accountId;
    }
}