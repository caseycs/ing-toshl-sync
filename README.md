# ing-toshl-sync
Scrape your ING transactions from the website and push them into Toshl

Toshl https://toshl.com is an awesome personal finance tool, however it's soooo boring to add
all your incomes/expences manually...

So here is some automation for it.

Unfortunately it requires quite a lot of tricks around to make it work really smooth, read below.

## how-to

Of course register Toshl account! Then get your personal API token: https://developer.toshl.com/labs/2016/04/personal-tokens/ 

Create `.env` file like this:

```
TOSHL_TOKEN=token
TOSHL_ACCOUNT_NAME="accound you want to sync"
```

You also need content of the page with your transactions.
You can always copy outer html from any webpage inspector.

Automatd way to get it is to have scraping browser extension,
but of course no one will use it for your bank account pages, unless you
haven't wrote it on your own. So I guess you already made it.

Put page content in the file `ingTransactionsPageSample.html`.

Run `php example.php`.

Enjoy!