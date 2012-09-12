<?php

namespace Schmutzka\Security;

use Nette\Security\AuthenticationException,
	Nette\Security\Identity;

class User extends \Nette\Security\User implements \Nette\Security\IAuthenticator
{

	/** @var \NotORM_Result */
	private $userModel;

	
	public function __construct(\Nette\Security\IUserStorage $storage, \Nette\DI\Container $context)
	{
		parent::__construct($storage, $context);
		$this->userModel = $context->models->user;
		if ($this->loggedIn && isset($context->params["logUserActivity"])) {
			$this->logUserActivity($context->params["logUserActivity"]);
		}
	}


	/**
	 * Identity property shortcut 
	 */
	public function &__get($name)
	{
		if ($this->getIdentity() && $this->getIdentity()->{$name} && $name != "roles") {
			$data = $this->getIdentity()->data;
			return $data[$name];

		}

		return \Nette\ObjectMixin::get($this, $name);
	}


	/**
	 * Update user identity data
	 * @param array
	 */
	public function updateIdentity(array $values)
	{
		foreach ($this->identity->data as $key => $value) {
			if (array_key_exists($key, $values)) {
				$this->identity->{$key} = $values[$key];
			}
		}
	}	


	/**
	 * Performs an authentication
	 * @param array
	 * @return IdentityEntity
	 */
	public function authenticate(array $credentials)
	{
        list($login, $password) = $credentials;

		$key[strpos($login, "@") ? "email" : "login"] = $login;
		
		$row = $this->userModel->item($key);

        if (!$row) {
            throw new AuthenticationException("Uživatel '$login' neexistuje.");
        }

        if (isset($row["auth"]) AND $row["auth"] != 1) {
            throw new AuthenticationException("Tento účet ještě nebyl autorizován. Zkontrolujte Vaši emailovou schránku.");
        }

		if (!preg_match('/^[0-9a-f]{40}$/i', $password)) { // sha1 check
			$password = sha1($password);
		}

        if ($row["password"] !== $password) {
            throw new AuthenticationException("Chybné heslo.");
        }

		unset($row["password"], $row["remindHash"]);

		return new Identity($row["id"], (isset($row["role"]) ? $row["role"] : "user"), $row);
	}


	/**
	 * Get user role
	 */
	public function getRole()
	{
		$roles = $this->roles;
		return array_pop($roles);
	}


	/**
	 * Log user activity
	 * @param array
	 */
	private function logUserActivity($configColumn)
	{
		$column = is_string($configColumn) ? $configColumn : "last_active"; 
		$array[$column] = new \Nette\DateTime;

		$lastActive =  $this->userModel->fetchSingle($column, $this->id); // 1 ms
		$lastUpdate = time()-strtotime($lastActive); 

		if ($lastUpdate > 180) { // log max once per 3 mins
			$this->userModel->update($array, array("id" => $this->id)); // 60 ms!
		}
	}
	
}