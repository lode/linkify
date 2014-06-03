/**
 * Function that linkifies links in text.
 *
 * It mainly looks for either parts that start with `http(s)://` or `www.`.
 * For details, see the php version.
 * 
 * Unicode support via xregexp
 * @see http://stackoverflow.com/questions/13210194/javascript-regex-equivalent-of-a-za-z-using-pl
 * @see http://xregexp.com/plugins/
 */
function linkify(text) {
	// exact copy of the regex in the www-repo, with the following differences:
	// - input slashes (first regex) are encoded as '&#x2F;'
	// - output slashes (both regex) should be escaped
	
	var urlLinkingRegex = XRegExp('(^|[\\(\\[\\s]+)(((http(s)?:&#x2F;&#x2F;)|(www\\.))((www\\.)?[-\\p{L}0-9\\.&#x2F;?=&#%+~_()\\[\\]!:,.;]+[^\\s\\.?!:,;\\)\\]]))($|[\\s\\.?!:,;\\)\\]]+)', 'gim');
	var urlLinkingReplacement = '$1<a href="http$5://$6$7" rel="nofollow" target="_blank">$2</a>$9';
	
	var bracketFixRegex = /(<a href=".+)(" rel="nofollow" target="_blank">.+)(\(|\[)(.*?)(<\/a>)(\)|\])/gim;
	var bracketFixReplacement = '$1$6$2$3$4$6$5';
	
	return text
		.replace(urlLinkingRegex, urlLinkingReplacement)
		.replace(bracketFixRegex, bracketFixReplacement);
}
