# ing-toshl-sync
Scrape your ING transactions from the website and push them into Toshl

Toshl https://toshl.com is an awesome personal finance tool, however it's soooo boring to add
all your incomes/expences automatically!

So i build some automation for it..

## how-to

Register Toshl account. Aquire personal API token: https://developer.toshl.com/labs/2016/04/personal-tokens/ 

Create `.env` file like this:

```
TOSHL_TOKEN=token
TOSHL_ACCOUNT_NAME="your accound name in toshl"
```

Then you need a HTML of your page with the transactions history. Simplest way to get it is to have
scraping browser extension, but of course no one will use it for your bank account pages, unless you
haven't wrote it on your own. So I guess you already made  it.

Anyway - you can always copy outer html from any webpage inspector.
  
Put the content in the file `ingTransactionsPageSample.html`.

Run `php example.php`.

Enjoy!