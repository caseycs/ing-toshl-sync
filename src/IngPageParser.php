<?php
namespace Ing2Toshl;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class IngPageParser
{
    /**
     * @var LoggerInterface
     */
    public $log;

    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }

    /**
     * @return IngTransaction[]|Collection
     */
    public function extractTransactions($html)
    {
        $crawler = new Crawler($html);
        $transactions = $crawler->filter('table#receivedTransactions tr')->each(
            function (Crawler $tr, $i) {
                //date
                $tdDate = $tr->filter('td')->getNode(0);
                if (!$tdDate) {
                    $this->log->warning('Empty date TD, skipping', ['trIndex' => $i]);
                    return false;
                }

                $dateRaw = trim($tdDate->nodeValue);
                try {
                    $date = Carbon::createFromFormat('d-m-Y', $dateRaw);
                } catch (\Exception $e) {
                    $this->log->warning('Date parsing failed, skipping', ['trIndex' => $i, 'date' => $dateRaw]);
                    return false;
                }

                //type
                $tdType = $tr->filter('td')->getNode(2);
                if (!$tdType) {
                    $this->log->warning('Empty type, skipping', ['trIndex' => $i]);
                    return false;
                }
                $type = trim($tdType->nodeValue);

                //comment
                $tdComment = $tr->filter('td')->getNode(1);
                if (!$tdComment) {
                    $this->log->warning('Empty comment, skipping', ['trIndex' => $i]);
                    return false;
                }
                $commentRaw = trim($tdComment->nodeValue);
                $commentLines = array_filter(array_map('trim', explode(PHP_EOL, $commentRaw)));
                $comment = join(PHP_EOL, $commentLines);

                //direction
                $tdAmountDirection = $tr->filter('td')->getNode(3);
                if (!$tdAmountDirection) {
                    $this->log->warning('Empty direction, skipping', ['trIndex' => $i]);
                    return false;
                }

                list($amountRaw, $directionRaw) = explode(' ', trim($tdAmountDirection->nodeValue), 2);
                $amount = str_replace('.', '', trim($amountRaw));
                $amount = str_replace(',', '.', $amount);
                $amount = (float)$amount;

                $ingTransaction = new IngTransaction(
                    $date,
                    $comment,
                    $type,
                    trim($directionRaw) === 'Bij' ? $amount : -$amount
                );

                $this->log->info('Transaction parsed', get_object_vars($ingTransaction));

                return $ingTransaction;
            }
        );

        /** @var IngTransaction[] $ingTransactions */
        $ingTransactions = new \Illuminate\Support\Collection(array_filter($transactions));

        return $ingTransactions;
    }
}