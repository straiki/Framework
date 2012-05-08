<?php

namespace Models;

use Nette\Utils\Strings;
use Nette\Diagnostics\IBarPanel;

class Panel implements IBarPanel
{

	/**
	 * Singleton instance
	 * Marked only as protected to allow extension of the class.
	 * To extend, simply override {@link getInstance()}.
	 * @var NotOrmPanel
	 */
	private static $_instance = null;

	/** @var array */
	private $queries = array();

	/** @int celkový čas příkazů */
	private $totalTime;

	/** @int celkový čas příkazů */
	private $times = NULL;


	/**
	 * Constructor
	 * Instantiate using {@link getInstance()}; NotOrmPanel is a singleton object.
	 * @return void
	 */
	public function __construct()
	{
		if(!isset($_SESSION["NotORM_timer"])) {
			$_SESSION["NotORM_timer"] = array();
		}
	}


	/**
	 * Enforce singleton; disallow cloning
	 * @return void
	 */
	private function __clone()
	{

	}


	/**
	 * Singleton instance
	 * @return NotOrmPanel
	 */
	public static function getInstance()
	{
		if(null === self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public function getId()
	{
		return 'NotORM';
	}


	public function getTab()
	{
		$this->totalTime = array_sum($_SESSION["NotORM_timer"]);
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAHpJREFUOMvVU8ENgDAIBON8dgY7yU3SHTohfoQUi7FGH3pJEwI9oBwl+j1YDRGR8AIzA+hiAIxLsoOW1R3zB9Cks1VKmaQWXz3wHWEJpBbilF3wivxKB9OdiUfDnJ6Q3RNGyWp3MraytbKqjADkrIvhPYgSDG3itz/TBsqre3ItA1W8AAAAAElFTkSuQmCC">' . count($this->queries) . (count($this->queries) != 1 ? ' queries' : 'query').' ('.$this->totalTime.' ms)';
	}

	public function getPanel()
	{
		if(count($this->queries) == 0) {
			return NULL;
		}

		$i = 0;
		$queries = $this->queries;

	
		unset($this->times);
		$this->times = $_SESSION["NotORM_timer"];
		$this->totalTime = array_sum($_SESSION["NotORM_timer"]);
		unset($_SESSION["NotORM_timer"]);

		foreach($queries as $key => $value) {
			$queries[$key]["sql"] = self::highlight($value["sql"]);
			$queries[$key]["time"] = (isset($this->times[$i]) ? $this->times[$i] : 0); // nezachytí všechny, prozatím doplní 
			$i++;
		}

		// nejde nějak přes šablonu?
		$totalTime = $this->totalTime;

		ob_start();
		require_once __DIR__ . "/templates/notorm.latte";
		return ob_get_clean();
	}


	/**
	 * Adds query and params to bank
	 * @param string
	 * @param array
	 **/
	public function logQuery($query, array $params = NULL)
	{
		$query = $this->combine($query, $params);
		$query = $this->highlight($query);

		$this->queries[] = array(
			"sql" => $query
		);
	}	


	/**
	 * Joins pure query and params to final line
	 * @param string query
	 * @param array params
	 * @return string final line 
	 */
	private function combine($query, $params = NULL) {
		foreach($params as $key => $param) {
			$params[$key] = "'$param'";
		}

		if($params) {
			$blanks = array_fill(0,count($params),"?");
			return str_replace($blanks, $params, $query);
		}
		return $query;
	}


	/**
	 * Higlights SQL query
	 * @param string query
	 * @return string query with higlighted keywords
	 */
	private function highlight($sql)
	{
		$keywords1 = 'CREATE\s+TABLE|CREATE(?:\s+UNIQUE)?\s+INDEX|SELECT|UPDATE|INSERT(?:\s+INTO)?|REPLACE(?:\s+INTO)?|DELETE|FROM|WHERE|HAVING|GROUP\s+BY|ORDER\s+BY|LIMIT|OFFSET|SET|VALUES|LEFT\s+JOIN|INNER\s+JOIN|TRUNCATE';
		$keywords2 = 'ALL|DISTINCT|DISTINCTROW|AS|USING|ON|AND|OR|IN|IS|NOT|NULL|LIKE|TRUE|FALSE|INTEGER|CLOB|VARCHAR|DATETIME|TIME|DATE|INT|SMALLINT|BIGINT|BOOL|BOOLEAN|DECIMAL|FLOAT|TEXT|VARCHAR|DEFAULT|AUTOINCREMENT|DESC|PRIMARY\s+KEY';

		// insert new lines - too dizzy
		$sql = " $sql ";

		// reduce spaces
		$sql = Strings::replace($sql, '#[ \t]{2,}#', " ");

		$sql = wordwrap($sql, 100);
//		$sql = htmlSpecialChars($sql);
		$sql = Strings::replace($sql, "#([ \t]*\r?\n){2,}#", "\n");
		$sql = Strings::replace($sql, "#VARCHAR\\(#", "VARCHAR (");

		// syntax highlight
		$sql = Strings::replace($sql,
						"#(/\\*.+?\\*/)|(\\*\\*.+?\\*\\*)|(?<=[\\s,(])($keywords1)(?=[\\s,)])|(?<=[\\s,(=])($keywords2)(?=[\\s,)=])#s",
						function ($matches) {
							if (!empty($matches[1])) // comment
								return '<em style="color:gray">' . $matches[1] . '</em>';

							if (!empty($matches[2])) // error
								return '<strong style="color:red">' . $matches[2] . '</strong>';

							if (!empty($matches[3])) // most important keywords
								return '<strong style="color:blue">' . $matches[3] . '</strong>';

							if (!empty($matches[4])) // other keywords
								return '<strong style="color:green">' . $matches[4] . '</strong>';
						}
		);


		$sql = trim($sql);
		return "<span class='dump'>$sql</span>";
		// return '<pre class="dump">' . $sql . "</pre>\n";
	}

}
