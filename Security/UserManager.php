<?php

namespace Schmutzka\Security;

use Nette;
use Nette\Utils\Strings;
use Nette\Security\AuthenticationException as AE;
use NotORM;

class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($login, $password) = $credentials;
		$key[strpos($login, "@") ? "email" : "login"] = $login;
		$row = $this->userModel->item($key);

		if (!$row) {
			throw new AE("Uživatel '$login' neexistuje.", self::IDENTITY_NOT_FOUND);
		}

		if (isset($row["auth"]) AND $row["auth"] != 1) {
			throw new AE("Tento účet ještě nebyl autorizován. Zkontrolujte Vaši emailovou schránku.");
		}

		if ($row["password"] !== $this->calculateHash($password, $row["password"])) {
			throw new AE("Chybné heslo.", self::INVALID_CREDENTIAL);
		}

		unset($row['password']);
		return new Nette\Security\Identity($row['id'], $row['role'], $row);
	}


	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($password === Strings::upper($password)) { // perhaps caps lock is on
			$password = Strings::lower($password);
		}
		return crypt($password, $salt ?: "$2a$07$" . Strings::random(22));
	}

}
