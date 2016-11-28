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
            $this->cachePerDay[$key] = parent::getTransactionsForDate($date);
        } else {
            $this->log->info('Transactions from cache for', ['date' => $date->format('Y-m-d')]);
        }
        return $this->cachePerDay[$key];
    }
}