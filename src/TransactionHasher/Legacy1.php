<?php
namespace Ing2Toshl\TransactionHasher;

use Ing2Toshl\IngTransaction;

class Legacy1 implements TransactionHasherInterface
{
    public $ingTransaction;

    public function name()
    {
        return 'firstLinesHash';
    }

    public function isLegacy()
    {
        return true;
    }

    public function hash(IngTransaction $ingTransaction)
    {
        $commentTwoLines = join(PHP_EOL, array_slice(explode(PHP_EOL, $ingTransaction->comment), 0, 3));
        return sha1($commentTwoLines);
    }
}