<?php
namespace Ing2Toshl;

use Illuminate\Support\Collection;
use Ing2Toshl\TransactionHasher\TransactionHasherInterface;
use Psr\Log\LoggerInterface;

class Processor
{
    /**
     * @var TransactionHasherInterface[]|Collection
     */
    private $ingTransactionHashers;

    public function __construct(
        ToshlClient $toshlClient,
        Collection $ingTransactionHashers,
        LoggerInterface $log
    ) {
        $this->toshlClient = $toshlClient;
        $this->ingTransactionHashers = $ingTransactionHashers;
        $this->log = $log;
    }

    /**
     * @param Collection $ingTransactions
     * @throws \Exception
     */
    public function addMissingTransactionToToshl($toshAccountName, Collection $ingTransactions)
    {
        $accountId = $this->toshlClient->findAccountId($toshAccountName);
        $transactionsCreated = 0;
        $transactionsUpdated = 0;

        /** @var IngTransaction[] $ingTransactions */
        foreach ($ingTransactions as $ingTransaction) {
            $this->log->info('Trying to match', get_object_vars($ingTransaction));

            $toshTransaction = $this->findTransaction($ingTransaction);
            $hashes = $this->calculateActualHashes($ingTransaction);

            if ($toshTransaction) {
                $toshTransactionExtra = isset($toshTransaction['extra']) ? $toshTransaction['extra'] : [];

                if ($toshTransactionExtra != $hashes) {
                    $this->log->debug(
                        'Hashes update required',
                        ['found' => $toshTransactionExtra, 'calculated' => $hashes]
                    );
                    $this->toshlClient->updateTransactionHashes($toshTransaction, $hashes);
                    $transactionsUpdated++;
                } else {
                    $this->log->debug('Hashes update not required');
                }
            } else {
                $this->toshlClient->addTransaction(
                    $accountId,
                    $ingTransaction->date,
                    $ingTransaction->amount,
                    $ingTransaction->comment,
                    $hashes
                );
            }
        }

        $this->log->info(
            'All transactions synced',
            ['transactionsCreated' => $transactionsCreated, 'transactionsUpdated' => $transactionsUpdated]
        );
    }

    private function findTransaction(IngTransaction $ingTransaction)
    {
        foreach ($this->ingTransactionHashers as $ingTransactionHasher) {
            $toshTransactions = $this->toshlClient->findTransactions(
                $ingTransaction->date,
                $ingTransactionHasher->name(),
                $ingTransactionHasher->hash($ingTransaction)
            );

            $this->log->debug(
                'Hasher match result',
                ['hasher' => $ingTransactionHasher->name(), 'found' => $toshTransactions->count()]
            );

            if ($toshTransactions->count() > 1) {
                $this->log->critical(
                    'Multiple matching transactions found, skipping',
                    [
                        'hasher' => $ingTransactionHasher->name(), 'transaction' => get_object_vars($ingTransaction)
                    ]
                );
                continue;
            } elseif ($toshTransactions->count() === 1) {
                $this->log->info('transaction found', $toshTransactions->first());
                return $toshTransactions->first();
            }
        }
        return null;
    }

    private function calculateActualHashes(IngTransaction $ingTransaction)
    {
        return $this->ingTransactionHashers->reduce(
            function (&$data, TransactionHasherInterface $hasher) use ($ingTransaction) {
                if (!$hasher->isLegacy()) {
                    $data[$hasher->name()] = $hasher->hash($ingTransaction);
                }
                return $data;
            },
            []
        );
    }
}