<?php
namespace Ing2Toshl\TransactionHasher;

use Ing2Toshl\IngTransaction;

class Legacy2 implements TransactionHasherInterface
{
    public function name()
    {
        return 'commentStartHash';
    }

    public function isLegacy()
    {
        return true;
    }

    public function hash(IngTransaction $ingTransaction)
    {
        preg_replace('~\s+~', ' ', trim($ingTransaction->comment));
        $firstWords = array_slice(explode(' ', $ingTransaction->comment), 0, 10);
        return sha1(join(' ', $firstWords));
    }
}