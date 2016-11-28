<?php
namespace Ing2Toshl;

class IngTransaction
{
    /**
     * @var \Carbon\Carbon
     */
    public $date;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $type;

    public function __construct(\Carbon\Carbon $date, $comment, $type, $amount)
    {
        $this->date = $date;
        $this->comment = $comment;
        $this->amount = $amount;
        $this->type = $type;
    }
}