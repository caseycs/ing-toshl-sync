# ing-toshl-sync
Scrape your ING transactions from the website and push them into Toshl

Toshl https://toshl.com is an awesome personal finance tool, however it's soooo boring to add
all your incomes/expences manually.

So here is some automation for it.

Unfortunately it requires quite a lot of tricks around to make it work really smooth, however half-manual solution if quite simple.

## Howto

Of course register Toshl account! Then get your personal API token: https://developer.toshl.com/labs/2016/04/personal-tokens/ 

Create `.env` file like this:

```
TOSHL_TOKEN=token
TOSHL_ACCOUNT_NAME="account name you want to sync"
```

You also need content of the page with your transactions, easiest way is to copy `<body>` outer html with the Firefox/Chrome page inspector.

Put page content in the file `ingTransactionsPageSample.html`.

Run `php example.php`.

Enjoy!

## The way of true Jedi

Please notice, that raw HTTP scraper for ING pages is really hard to implement becuase of ajax, Angular, complicated login flow etc. Also that will mean having your password somewhere else. So you either go for the browser emulation or (way simpler and faster) you make a Chrome extension.

Let's assume you have your own scraping browser extension, which can send content of any page to your private server.

On your private server you have for example Jenkins, and the request from extension is triggering some job which is leading to running Tosh syncing script (based on `example.php`) against page content. 

So you just go to your private banking, login with your favourite&secure password manager, open last transactions page, activate your extension - and that's it!

If you will go really hardcore, you can even receive notification in your private (or home, or family) Slack team with all the stats :)
