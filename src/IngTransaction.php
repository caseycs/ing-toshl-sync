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

    public function firstLinesHash()
    {
        $commentTwoLines = join(PHP_EOL, array_slice(explode(PHP_EOL, $this->comment), 0, 3));
        return sha1($commentTwoLines);
    }

    public function commentStartHash()
    {
        preg_replace('~\s+~', ' ', trim($this->comment));
        $firstWords = array_slice(explode(' ', $this->comment), 0, 10);
        return sha1(join(' ', $firstWords));
    }
}