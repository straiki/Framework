<?php

namespace Schmutzka\Security;

use Nette;

class User extends Nette\Security\User
{
	/** @inject @var Schmutzka\Models\User */
	public $userModel;


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
	 * Get user role
	 * @return  string
	 */
	public function getRole()
	{
		$roles = $this->roles;
		return array_pop($roles);
	}


	/**
	 * Log user activity
	 */
	public function logUserActivity()
	{
		$array["last_active"] = new Nette\DateTime;

		$lastActive =  $this->userModel->fetchSingle($column, $this->id);
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
