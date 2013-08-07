<?php

namespace AdminModule;

class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		if ($this->paramService->gaLink) {
			$this->template->gaLink = $this->paramService->gaLink;
		}
	}


	// @todo
	public function renderInstall()
	{
		dd('do it now!');
	}

}
