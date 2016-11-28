<?php
namespace Ing2Toshl\TransactionHasher;

use Ing2Toshl\IngTransaction;

class FistCommentWords implements TransactionHasherInterface
{
    /**
     * @var int
     */
    public $wordsCount;

    public function __construct($wordsCount)
    {
        $this->wordsCount = $wordsCount;
    }

    public function isLegacy()
    {
        return false;
    }

    public function name()
    {
        return 'hashByDateAmountFirst' . $this->wordsCount . 'WordsOfComment';
    }

    public function hash(IngTransaction $ingTransaction)
    {
        $parts = [$ingTransaction->date->format('Y-m-d'), $ingTransaction->amount];
        $parts += array_slice(explode(PHP_EOL, $ingTransaction->comment), 0, $this->wordsCount);
        return sha1(json_encode($parts));
    }
}