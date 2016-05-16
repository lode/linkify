# Linkify

Finding and linkifying things that look like links.

It focuses on: a main use case, short and fast code, and code-sharing between backend and frontend.

This package works for multiple languages, right now php and js.


## Install

[Use Composer](http://getcomposer.org/). And use require to get the latest stable version:

```
composer require alsvanzelf/linkify:dev-master
```


## Features

The basics:

- Anything that starts with `http://`, `https://` or `www.`
- No support for ftp, email addresses, basic auth, and other edge cases.
- No advanced validation, for example real tld's.
- Adds `http://` for the href when it was not given in the text.

Things that work:

- Case insensitive
- Unicode
- Paths, query strings, hashes, and other allowed characters
- Urls starting/ending a line or the whole string
- Not including punctuation after the url
- Urls enclosed in brackets
- Urls with brackets inside the url

Things that -currently- do not work:

- Urls which contain one of: @ |


## Usage

``` php
$linkifier = new alsvanzelf\linkify\linkify();
$linkified = $linkifier->linkify($plain);
```

``` js
var linkified = linkify(plain);
```

Note the input string should NOT contain html to prevent XSS.
As afterwards one can not differentiate the added anchors from existing html.


## Contribute

Pull Requests or issues are welcome!

For example on adding more languages, or perfecting the cases catched.


## License

[MIT](/LICENSE)


## Thanks

Thanks to inspiration from:

- http://blog.codinghorror.com/the-problem-with-urls/
- https://github.com/misd-service-development/php-linkify
- http://daringfireball.net/2010/07/improved_regex_for_matching_urls
- http://daringfireball.net/misc/2010/07/url-matching-regex-test-data.text
- https://mathiasbynens.be/demo/url-regex
