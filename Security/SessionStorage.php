<?php

namespace Schmutzka\Http;

use Nette;
use Nette\Http\ISessionStorage;
use NotORM;


/**
 * @lock see: http://forum.nette.org/cs/12874-ukladani-sesion-dp-databaze-je-to-dobry-napad
 */
class SessionStorage implements ISessionStorage
{
	/** @var NotORM */
	private $database;


	public function __construct(NotORM $database)
	{
		$this->database = $database;
	}


	/**
	 * @param  string
	 * @param  string
	 * @return bool
	 */
	public function open($savePath, $sessionName)
	{
		if ($this->database) {
			return TRUE;
		}

		return FALSE;

		/*
		$id = session_id();
        while(!$this->database->query("SELECT IS_FREE_LOCK('session_$id') AS lo")->fetch()->lo);
        $this->database->exec("SELECT GET_LOCK('session_$id', 160)");
		*/
	}


	public function close()
	{
		/*
		$id = session_id();
        $this->database->exec("SELECT RELEASE_LOCK('session_$id')");
        return true;
		*/
	}


	/**
	 * @param  string
	 * @return string
	 */
	public function read($id)
	{
		$data = $this->database->session->where('id', $id)
			->fetch('data');

		if ($data) {
			return $data;
		}

		return '';
	}


	/**
	 * @param  string
	 * @param  string
	 */
	public function write($id, $data)
	{
		$this->database->session->where('id', $id)
			->delete();

		$record = array(
			'id' => $id,
			'time' => time(),
			'data' => $data
		);

		$this->database->session->insert($record);
	}


	/**
	 * @param  string
	 * @param  bool
	 */
	public function destroy($id)
	{
		return $this->database->session->where('id', $id)
			->delete();
	}


	/**
	 * @param  int
	 * @return  bool
	 */
	public function clean($maxlifetime)
	{
		return $this->database->session->where('time < ?', (time() - $max))
			->delete();
	}





	/**
	 * @param  string
	 */
	public function remove($id)
	{
		$this->database->session->where('id', $id)
			->delete();
	}


	/********************** helpers **********************/


	private function checkStorageAccess()
	{
		if ($this->database == NULL) {
			throw new Nette\InvalidStateException('The connection to database for session storage is not open!');
		}
	}

}
