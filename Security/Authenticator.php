<?php

namespace Schmutzka\Security;

use Nette\Object,
	Nette\Security\IAuthenticator,
	Nette\Security\AuthenticationException,
	Nette\Security\Identity;

class Authenticator extends Object implements IAuthenticator
{
	/** @var \NotORM_Result */
	private $userTable;

    function __construct(\NotORM_Result $userTable)
    {
        $this->userTable = $userTable;
    }


	/**
	 * Performs an authentication
	 * @param array
	 * @return IdentityEntity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {

        list($login, $password) = $credentials;
		$row = $this->userTable->where(strpos($login, "@") ? "email" : "login", $login);

        if (!$row->count("*")) { // check existance
            throw new AuthenticationException("Uživatel '$login' neexistuje.");
        }

		$row = $row->fetchRow();

        if (isset($row["auth"]) AND $row["auth"] != 1) { // check authentication
            throw new AuthenticationException("Tento účet ještě nebyl autorizován. Zkontrolujte Vaši emailovou schránku.");
        }

		// password		
        if($row["password"] !== sha1($password)) {
            throw new AuthenticationException("Chybné heslo.");
        }

		unset($row["password"], $row["remindHash"]);

		return new Identity($row["id"], (isset($row["role"]) ? $row["role"] : "user"), $row);
	}
}