<?php

namespace Schmutzka\Security;

use Nette;
use Nette\Security\AuthenticationException as AE;
use Schmutzka\Utils\Password;
use Schmutzka\Models;

class User extends Nette\Security\User implements Nette\Security\IAuthenticator
{
	/** @var Models\User */
	protected $userModel;

	/** @var string */
	protected $salt;


	/**
	 * @param Nette\Security\IUserStorage
	 * @param Nette\DI\Container
	 * @param Models\User
	 */
	public function __construct(Nette\Security\IUserStorage $storage, Nette\DI\Container $context, Models\User $userModel)
	{
		parent::__construct($storage, $context);
		$this->userModel = $userModel;
		if (isset($context->parameters["salt"])) {
			$this->salt = $context->parameters["salt"];
		}
	}


	/**
	 * Identity property shortcut
	 * @param string
	 */
	public function &__get($name)
	{
		if ($this->getIdentity() && array_key_exists($name, $this->getIdentity()->data) && $name != "roles") {
			$data = $this->getIdentity()->data;
			return $data[$name];
		}

		return Nette\ObjectMixin::get($this, $name);
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
			throw new AE("Uživatel '$login' neexistuje.");
		}

		if (isset($row["auth"]) AND $row["auth"] != 1) {
			throw new AE("Tento účet ještě nebyl autorizován. Zkontrolujte Vaši emailovou schránku.");
		}

		if (!preg_match('/^[0-9a-f]{40}$/i', $password)) {
			$password = Password::saltHash($password, $this->salt);
		}

		if ($row["password"] !== $password) {
			throw new AE("Chybné heslo.");
		}

		unset($row["password"]);
		return new Nette\Security\Identity($row["id"], (isset($row["role"]) ? $row["role"] : "user"), $row);
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
	public function logUserActivity($configColumn)
	{
		$column = is_string($configColumn) ? $configColumn : "last_active"; 
		$array[$column] = new \Nette\DateTime;

		$lastActive =  $this->userModel->fetchSingle($column, $this->id); // 1 ms
		$lastUpdate = time() - strtotime($lastActive); 

		if ($lastUpdate > (3 * 60)) { // log max once per 3 mins
			$this->userModel->update($array, $this->id); // 60 ms!
		}
	}


	/**
	 * Automated login
	 * @param string
	 */
	public function autologin($user)
	{
		if (!($user instanceof User)) {
			$user = $this->userModel->item($user);
		}
		unset($user["password"]);

		$identity = new Nette\Security\Identity($user["id"], (isset($user["role"]) ? $user["role"] : "user"), $user);
		$this->user->login($identity); // fix
	}
	
}