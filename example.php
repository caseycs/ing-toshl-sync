<?php
use Ing2Toshl\IngPageParser;
use Ing2Toshl\Processor;
use Ing2Toshl\ToshlClientCached;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require 'vendor/autoload.php';

//read .env
(new Dotenv\Dotenv(__DIR__))->load();

// log handler
$logHandler = new StreamHandler('php://output', Logger::INFO);

//processing
(new Processor())
    ->addMissingTransactionToToshl(
        new ToshlClientCached(
            getenv('TOSHL_TOKEN'),
            (new Logger('toshl'))->pushHandler($logHandler)
        ),
        getenv('TOSHL_ACCOUNT_NAME'),
        (new IngPageParser((new Logger('ing'))->pushHandler($logHandler)))
            ->extractTransactions(file_get_contents('ingTransactionsPageSample.html')),
        (new Logger('processor'))->pushHandler($logHandler)
    );