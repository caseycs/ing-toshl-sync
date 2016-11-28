<?php
namespace Ing2Toshl\TransactionHasher;

use Ing2Toshl\IngTransaction;

interface TransactionHasherInterface
{
    public function name();

    public function isLegacy();

    public function hash(IngTransaction $ingTransaction);
}