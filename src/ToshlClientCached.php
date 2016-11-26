<?php
namespace Ing2Toshl;

use Carbon\Carbon;

class ToshlClientCached extends ToshlClient
{
    /**
     * @var array
     */
    protected $cachePerDay;

    public function getTransactionsForDate(Carbon $date)
    {
        $key = $date->format('Y-m-d');
        if (empty($this->cachePerDay[$key])) {
            $this->log->info('Transactions from cache for', ['date' => $date->format('Y-m-d')]);
            $this->cachePerDay[$key] = parent::getTransactionsForDate($date);
        }
        return $this->cachePerDay[$key];
    }
}