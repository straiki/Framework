<?php

namespace Schmutzka\Security;

use Nette\Security\IUserStorage,
	Nette\Security\Identity,
	Nette\Security\AuthenticationException;

class User extends \Nette\Security\User implements \Nette\Security\IAuthenticator
{
	/** @var \NotORM_Result */
	private $userModel;

	
	public function __construct(IUserStorage $storage, \Nette\DI\Container $context)
	{
		parent::__construct($storage, $context);
		$this->userModel = $context->models->user;
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
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
        list($login, $password) = $credentials;

		$key[strpos($login, "@") ? "email" : "login"] = $login;
		$row = $this->userModel->item($key);


        if (!$row) { // check existance
            throw new AuthenticationException("Uživatel '$login' neexistuje.");
        }

        if (isset($row["auth"]) AND $row["auth"] != 1) { // check authentication
            throw new AuthenticationException("Tento účet ještě nebyl autorizován. Zkontrolujte Vaši emailovou schránku.");
        }

        if ($row["password"] !== sha1($password)) {
            throw new AuthenticationException("Chybné heslo.");
        }

		unset($row["password"], $row["remindHash"], $row["role"]);

		return new Identity($row["id"], (isset($row["role"]) ? $row["role"] : "user"), $row);
	}


	
}