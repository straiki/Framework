<?php

namespace AdminModule;


class HomepagePresenter extends BasePresenter
{

	public function renderDefault()
	{
		if (isset($this->paramService->gaLink) && $this->paramService->gaLink) {
			$this->template->gaLink = $this->paramService->gaLink;
		}
	}


	// @todo
	public function renderInstall()
	{
		dd('do it now!');
	}

}
