<?php

namespace alsvanzelf\linkify;

class linkify {

protected static $defaults = array(
	'rel_nofollow' => true,
	'target_blank' => true,
	'class_names'  => '',
);

private $attributes = '';
private $matchers   = array();

public function __construct(array $options=array()) {
	$this->prepare_attributes($options);
	$this->prepare_matchers();
}

/**
 * creates html links for all link-a-like parts of a text
 * 
 * @note the input string should NOT contain html to prevent XSS
 *       afterwards one can not differentiate the added anchors from existing html
 * 
 * @param  string  $plain input string, optionally multiline
 * @return string         input with links inside anchors
 */
public function linkify($plain) {
	$linkified = $plain;
	
	foreach ($this->matchers as $matcher) {
		list($regex, $replacement) = $matcher;
		$linkified = preg_replace($regex, $replacement, $linkified);
	}
	
	return $linkified;
}

private function prepare_attributes(array $options) {
	$attributes = array();
	
	if (isset($options['rel_nofollow']) === false || $options['rel_nofollow'] === true) {
		$attributes[] = 'rel="nofollow"';
	}
	
	if (isset($options['target_blank']) === false || $options['target_blank'] === true) {
		$attributes[] = 'target="_blank"';
	}
	
	if (empty($options['class_names']) === false && is_string($options['class_names']) === true) {
		if (strpos($options['class_names'], '$')) {
			throw new \Exception('can not use $ in class names to prevent mixing with regex replacement');
		}
		
		$attributes[] = 'class="'.$options['class_names'].'"';
	}
	
	if (empty($attributes)) {
		return;
	}
	
	$this->attributes = ' '.implode(' ', $attributes);
}

private function prepare_matchers() {
	/**
	 * determine what punctuation can be used directly after a url
	 * if one of these characters ends the url, it is kept outside the anchor
	 */
	$regex_punctuation = '\.?!:,;\)\]\<';
	
	/**
	 * main regex, comments are inline
	 */
	$regex = '{
		# before the url should be: start of string; whitespace; or opening bracket ($1)
		(^|[\(\[\>\s]+)
		
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
				[-\p{L}0-9\./?=&#%+~_@()\[\]!:,.;]+
				
				# url should not end with whitespace or punctuation
				# this way we can separate that from the anchor
				[^\s'.$regex_punctuation.']
			)
		)
		
		# after the url should be: end of string; whitespace; or punctuation ($9)
		($|[\s'.$regex_punctuation.']+)
	
	# modifiers: case insensitive; multiline; allow whitespace and comments; use unicode
	}imxu';
	
	$replacement = '$1<a href="http$5://$6$7"'.$this->attributes.'>$2</a>$9';
	
	$this->matchers[] = array($regex, $replacement);
	
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
	$regex = '{(<a href=".+)("'.$this->attributes.'>.+)(\(|\[)(.*?)(</a>)(\)|\])}imu';
	$replacement = '$1$6$2$3$4$6$5';
	
	$this->matchers[] = array($regex, $replacement);
}

}
