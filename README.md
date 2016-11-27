# ing-toshl-sync
Scrape your ING transactions from the website and push them into Toshl

Toshl https://toshl.com is an awesome personal finance tool, however it's soooo boring to add
all your incomes/expences manually...

So here is some automation for it.

Unfortunately it requires quite a lot of tricks around to make it work really smooth, read below.

## Howto

Of course register Toshl account! Then get your personal API token: https://developer.toshl.com/labs/2016/04/personal-tokens/ 

Create `.env` file like this:

```
TOSHL_TOKEN=token
TOSHL_ACCOUNT_NAME="account name you want to sync"
```

You also need content of the page with your transactions.
You can always copy outer html from any webpage inspector.

Automated way to get it is to have scraping browser extension,
but of course no one will use it for your bank account pages, unless you
haven't wrote it on your own. So I guess you already made it.

Put page content in the file `ingTransactionsPageSample.html`.

Run `php example.php`.

Enjoy!

## Jedi path

Please notice, that raw HTTP scraper for ING pages is really hard to do becuase of ajax, angular, quite complicated login flow etc. Also also that will mean having your password somewhere else. So you either go for PhantomJS emulation OR (way simpler and faster) just make a Chrome extension.

Let's assume you have your own scraping browser extension, which can send content of any page to your private server when you activate it.

On your private server you have let's say Jenkins, and the request from extension is triggering some job. For the ING pages it might be resolved into Tosh syncing script, very similar to `example.php`. 

So you just go to your private banking, open last transactions, click on the extension icon - and that's it! If you go really hardcore, you can even receive notification in your private (home, family) Slack team with all the stats :)
