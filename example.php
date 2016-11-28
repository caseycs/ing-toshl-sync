<?php
use Illuminate\Support\Collection;
use Ing2Toshl\IngPageParser;
use Ing2Toshl\Processor;
use Ing2Toshl\ToshlClientCached;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require 'vendor/autoload.php';

//read .env
(new Dotenv\Dotenv(__DIR__))->load();

// log handler
$logHandler = new StreamHandler('php://output', Logger::DEBUG);

//processing
(new Processor(
    new ToshlClientCached(
        getenv('TOSHL_TOKEN'),
        false,
        (new Logger('toshl'))->pushHandler($logHandler)
    ),
    new Collection(
        [
            new \Ing2Toshl\TransactionHasher\Legacy1(),
            new \Ing2Toshl\TransactionHasher\Legacy2(),
            new \Ing2Toshl\TransactionHasher\FistCommentWords(10),
        ]
    ),
    (new Logger('processor'))->pushHandler($logHandler)
))->addMissingTransactionToToshl(
    getenv('TOSHL_ACCOUNT_NAME'),
    (new IngPageParser((new Logger('ing'))->pushHandler($logHandler)))
        ->extractTransactions(file_get_contents('ingTransactionsPageSample.html'))
);