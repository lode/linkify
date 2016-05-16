<?php

class linkify {

private static $regex_utf8_alpha = '\p{L}'; // any (unicode) letter

/**
 * creates html links for all link-a-like parts of a text
 * we focus on: a main use case; short and fast code; and code-sharing between php and js
 * we do not try to achieve perfection, although we think we come very close :)
 * 
 * the basics:
 * - anything that starts with http://, https:// or www.
 * - we do not support ftp, email addresses, basic auth, and other edge cases
 * - we do not check much more, for example real tld's
 * - we add http:// for the href when it was not given in the text
 * 
 * things that work:
 * - case insensitive
 * - unicode
 * - paths, query strings, hashes, and other allowed characters
 * - urls starting/ending a line or the whole string
 * - not including punctuation after the url
 * - urls enclosed in brackets
 * - urls with brackets inside the url
 * 
 * things that do not work:
 * - urls which contain one of: @ |
 * 
 * @note the input string should NOT contain html to prevent XSS
 *       afterwards one can not differentiate the added anchors from existing html
 * 
 * @param  string  $string               input (multiline) string
 * @return string                        string with links inside anchors
 * 
 * @see http://www.codinghorror.com/blog/2008/10/the-problem-with-urls.html
 */
public static function linkify($string) {
	/**
	 * determine what punctuation can be used directly after a url
	 * if one of these characters ends the url, it is kept outside the anchor
	 */
	$regex_punctuation = '\.?!:,;\)\]';
	
	$regex = '{
		# before the url should be: start of string; whitespace; or opening bracket ($1)
		(^|[\(\[\s]+)
		
		# actual full url ($2)
		(
			# check for http://, https:// or www. separatly
			# this way we can add the http:// in the href later
			(
				# use the s for replacement ($5)
				# and encoded slashes
				(http(s)?://)
				|
				# use the www. for replacement ($6)
				(www\.)
			)
			
			# the meat of the url ($7)
			(
				# it might start with www. if we started with http(s)://
				(www\.)?
				
				# allowed characters: alphanum (unicode), path, query string, hash and some others
				[-'.self::$regex_utf8_alpha.'0-9\./?=&#%+~_()\[\]!:,.;]+
				
				# url should not end with whitespace or punctuation
				# this way we can separate that from the anchor
				[^\s'.$regex_punctuation.']
			)
		)
		
		# after the url should be: end of string; whitespace; or punctuation ($9)
		($|[\s'.$regex_punctuation.']+)
	
	# modifiers: case insensitive; multiline; allow whitespace and comments; use unicode
	}imxu';
	
	$replacement = '$1<a href="http$5://$6$7" rel="nofollow" target="_blank">$2</a>$9';
	
	$output = preg_replace($regex, $replacement, $string);
	
	/**
	 * brackets are tricky characters as they are allowed inside urls but often used around urls
	 * examples: "http://en.wikipedia.nl/wiki/foo_(bar)" and "(http://example.com)"
	 * 
	 * we'll fix this wikipedia case separately
	 * 
	 * @note this does not work with bracket-enclosed urls with just a opening bracket inside the url and no closing one
	 *       doesn't work: "(http://en.wikipedia.nl/wiki/foo_(bar)" (the closing bracket will be added to the url)
	 *       does work:    "(http://en.wikipedia.nl/wiki/foo_(bar))" (only the first closing bracket will be added)
	 */
	$fixed = preg_replace('{(<a href=".+)(" rel="nofollow" target="_blank">.+)(\(|\[)(.*?)(</a>)(\)|\])}imu', '$1$6$2$3$4$6$5', $output);
	
	return $fixed;
}

}
