<?php

namespace Schmutzka\Socials;

use Nette\Utils\Strings,
	Nette\Caching\Cache;

class Twitter extends \Nette\Object 
{

	/** @var int */
	private $userId;

	/** @var \Cache */
	private $cache;

	/** @var int */
	private $count = 12;


	public function __construct(array $params, $cache)
	{
		$this->userId = $params["userId"];
		$this->cache = $cache;

		if(isset($params["count"])) {
			$this->count = $params["count"];
		}
	}


	/** 
	 * Load tweets
	 */
	public function getTweets()
	{
		// 24 h cached
		$tweetList = $this->cache->load("tweetList"); 
		if(!$tweetList) {

			// sesion 
			$doc = new \DomDocument();
			$url = "http://twitter.com/statuses/user_timeline/".$this->userId.".rss?count=".$this->count;
			$doc->load($url);
			$xpath = new \DOMXPath($doc);

			$tweets = $xpath->evaluate("/rss/channel/item");
		
			foreach($tweets as $tweet) {
				$timestamp = htmlspecialchars(($xpath->evaluate("string(pubDate)", $tweet)));
				$message = htmlspecialchars(($xpath->evaluate("string(description)", $tweet)));
				$message = ltrim($message,"sleepercz: ");

				if(strpos($message,"@") !== 0 AND strpos($message,"RT ") !== 0) { // is not reply or retweet
					$returnArray[] = array(
						"date" => date("j. n.",strtotime($timestamp)),
						"message" => self::twitterify(Strings::truncate(strtr($message,array("\"" => "")),135))
					);
				}
			}
	
			$tweetList = $returnArray;

			// save to cache
			$this->cache->save("tweetList", $tweetList, array(
				Cache::EXPIRE => "+ 12 hours"
			));
		}
		$returnArray = $tweetList;

		// necháme 2 náhodné 
		while(count($returnArray) > 2) {
			unset($returnArray[rand(0,7)]);
		}
		return $returnArray;
	}



	/**
	 * Linkify all links in message
	 * @param string
	 * @return twitterified text 
	 */
	private static function twitterify($s)
	{
		$s = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t\,< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $s); // http://
		$s = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r\,\"< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $s); // www
		$s = preg_replace("/@(\w+)/", "<a href=\"http://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $s); // twitter account
		$s = preg_replace("/#(\w+)/", "<a href=\"http://search.twitter.com/search?q=\\1\" target=\"_blank\">#\\1</a>", $s); // hashtag

		$s = preg_replace("/(j.mp\/[a-zA-Z0-9_]*)/", "<a href=\"http://\\1\" target=\"_blank\">\\1</a>", $s); // j.mp/*
		$s = preg_replace("/(bit.ly\/[a-zA-Z0-9_]*)/", "<a href=\"http://\\1\" target=\"_blank\">\\1</a>", $s); // bit.y/*

		return $s;
	}

}