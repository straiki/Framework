<?php

namespace EmailModule;

class SettingsPresenter extends \AdminModule\BasePresenter
{

	/**
	 * Email settings form
	 * @return EmailModule\Forms\SettingsForm
	 */
	public function createComponentSettingsForm()
	{
		return new Forms\SettingsForm($this->models->emailSettings, $this->user);
	}

}