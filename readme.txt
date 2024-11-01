=== SiteBrains Interactive Spam Blocker ===
Contributors: SiteBrains) 
Tags: Spam, anti-spam, antispam, spam blocker, client side, blog, bots, block bots, blog comments, block spam, comment, comments, NoSpamNX, plugin, protection, security, spam, spambot, spambots, comment spam, spam comments, captcha, spam filter, comment filter, sploggers, anti spam user, block, no captcha, recaptcha, spam blog, user spam, user generated content, ugc, defensio, nospamnx, blacklist, akismet, antispam bee, avh first defense, bad behavior, comment love, comment luv, growmap, mollom, si captcha anti-spam, spam free, comment spam filter, spammer, spam free wordpress, block spam by math reloaded, commentluv, spammer blocker, wp-spamfree, are you human, anti-captcha, captcha plugin, spam protection, profanity, profanity filter, content filter, content quality, content scoring, quality scoring, swearing, curses, cursing, swears, moderation, auto-moderation, text analysis, discussion, posts, forms, form validation, validations, comment validation, sabre, authentication, validate registrations, user registration, validation user registrations, seo
Requires at least: 2.1 
Tested up to: 3.2.1
Stable tag: trunk

SiteBrains blocks spam posts using a completely configurable set of rules. 
Both interactively as the user types and on our servers upon submission.

== Description ==

Block spam using state of the art client-side form validation. SiteBrains plug-in displays themed notifications to your users as they type.
Our analysis is signed using a secret key unique to your domain.
The signed analysis is submitted in a hidden field along with the rest of the form fields.
This makes it impossible to forge or circumvent, and ensures that the user's post came from your webpage.

No blacklists, no manual moderation, no waiting for a post to publish, and no captcha's... Just simple spam protection. 

That's 100% of bot spam, which makes up roughly 65% of ALL spam. 

Read on to find out how we detect the other 35 percent, the human generated spam:
	
Interactive Client side validation takes place on your page as your users typing their message, displaying tips and notifications that will help them compose a valid post that complies with the community rules that you have established (for civil discourse).
	
Our Dictionaries based tests detect misspellings, profanity/cursing/swearing, spam phrases, and abusive language, and product names.
Out with the garbage texts, hate speech, and obvious spam.
	
Our Natural language processor scores the Grammatical and Syntax coherence of texts, blocking poor quality content, non-English language, and gibberish.
	
A spam filter (Bayesian algorithm) of the type that is commonly used to block spam in emails is used to test the comment body and filter spam texts.
	
A useful set of Predefined Regular Expressions, as well as your custom rules are applied to detect common self promotion tokens left by some spammers, such as phone numbers and emails. 
This also protects your less savvy posters from identity theft and receiving mounds of email spam that results from exposing your email to the web.
	
Comments with embedded Links and URLs get special treatment. 
Links are the common thread across 99% of spam comments on one hand, but links make the Internet such a resourceful place... So we give those comments a really close examination:

SiteBrains extracts URLs from each field in the form..
Our servers fetch the text from those URLs following redirects, iFrames, and executing JavaScript.
We use an ever growing corpus of millions of pages that categorize over 50,000 topics to classify the text. 
Or in plain English, we invented a machine that can tell us what the text is about!
We compute the relevancy of the text to the page where the post was made taking into account your configurations.
If the text is not relevant enough or categorized as an hazardous topic (gambling and adult content) it is blocked. For example, an article about politics would most likely not want comments with links to websites selling T-shirts. We detect this lack of relevance to the article as well as the disparity in commercial intent between the article and target site and make a decision to block the url or comment on this basis
	
The last protection layer takes place on your servers, Verify the Validation Signature using a single call to our engine class which is available as part of our API in all major languages. 
This makes sure all of the previous layers were performed and md5 signed by our servers, and that the post came from your page and not a spam bot.

== Installation ==

The Wordpress implementation of SiteBrains Engine works right out of the box.
Download the plug-in in zip format or as a text file (rename to .php) and upload (copy) the plug-in file to /wp-content/SiteBrains/Index.php.

Alternatively, you can use the plug-in directory search utility at /wordpress/wp-admin/plugin-install.php, and click 'install now'

Once installed, remember to activate the plug-in. 
That's it! Your SiteBrains Wordpress plug-in is active!
No need to create any tags or make modifications to any template files.

To test, try to enter an invalid email field and click the cursor onto another field or area.
Note that while regex (text patterns) based rules are validated on the client side, the dictionaries lookups (spelling, foul language, products and spam), the grammatical analysis, the embedded URL relevancy tests and many other tests are performed on SiteBrains' servers.

To test those, try submitting a comment containing abusive or foul language or insert an url in your comment to an irrelevant or inappropriate website (think drugs, gambling, porn, etc.).

== Frequently Asked Questions ==

= What does it do? =

SiteBrains Spam Blocker Wordpress plug-in brings advanced client-side validation to Wordpress sites.
Show users feedback on their posts and comments as they type! Validate posts before they are submitted. 
Block spam, self promotion links and foul language using our cutting edge technology.
Use our advanced moderation tools to change your client-side notifications, including appearance, messages and triggers.

= Is this service free? =

Yes, SiteBrains spam blocker is free for non commercial use.
We just want to make the web a cleaner place.

= Where do the blocked comments go? =

From your management dashboard at SiteBrains you can access reports, charts and statistics of the comments that were blocked and approved.
On the Moderation queue report you will find all the blocked comments plus tools to edit them and re post them to your server. You must create an account and log-in to access the management dashboard and moderation queues.

Accessing the settings page

Once the SiteBrains plug-in is installed and activated you will have an additional entry under the 'Comments' menu titled 'SiteBrains', click it to open the settings screen.

= How do I access the settings page? =

Click 'Open SiteBrains Editor' to access the full set of moderation tools and utilities, enabling you to modify existing rules, create new rules, modify how broken rules are handled, customize your notifications and more.

= Can I customizing notifications' texts and appearances =

SiteBrains Wordpress plug-in exposes functionality to customize the look of the notifications displayed to your users.
Control every element of the notifications from texts to colors and timing of display via our online editor accessible from the SiteBrains plug-in settings page.

= How does it work? =

When a user submits a form, the texts on the form are first sent to the SiteBrain's servers for validation.
The response contains a detailed report in an XML format that is signed by a unique key assigned to only your site upon the initial plug-in activation.
If the overall result of the validation is negative, help tip and notifications are displayed to the user helping him to improve the text or remove unwanted elements.
Additionally, submissions can be blocked or allowed but modified prior to submission. For example, a comment that contains spam or consists of mostly foul language 
is typically blocked but a comment that contains a curse word or two may be simply edited to replace certain characters within curse words with ** such as “bull sh*t”. 
You can dial-up or down these rules and settings to satisfy your goals and fit your community.
If the overall result of the validation is positive, the submission of the form to your site is allowed, and the signed XML report is sent along with it.
On your site, the signature of the XML report is verified to make sure this report was indeed produce by the SiteBrains servers for your domain, and the fields in the received form are compared to the fields in the report. If any of these tests failed the message is blocked.
A good way to test your server side validation is to turn off Javascript on the browser and try to submit a form.

== Screenshots ==

1. A sample of a form validated by SiteBrains.
2. The Wordpress settings page

== Changelog ==

= 1.0.0 =
* Initial release.
= 6.1.4 =
* Stable release.
= 6.1.6 =
* Compatibility with predefined global table css styles.
= 6.1.9 = 
* Compatibility with activation of plugin.
= 6.2.1 =
* Improved relevancy classification and general stability.
= 6.2.2 =
* Added additional functionality and correct error in plugin.
= 6.3.0 =
* Corrected additional characters on some installations of wordpress, adjusted functionality for additional security and privacy.
= 6.3.5 =
* Improved activation flow.
= 6.3.7 =
* Selection of protection level in activation flow.

== Upgrade Notice ==

= 1.0.0 =
Initial release.

= 6.1.4 =
Stable release.

= 6.1.6 =
Compatibility with predefined global table css styles.

= 6.1.9 = 
Compatibility with activation of plugin.

= 6.2.1 =
Improved relevancy classification and general stability.

= 6.2.2 =
Added additional functionality and correct error in plugin.

= 6.3.0 =
Corrected additional characters on some installations of wordpress, adjusted functionality for additional security and privacy.

= 6.3.5 =
Improved activation flow.

= 6.3.7 =
Selection of protection level in activation flow.