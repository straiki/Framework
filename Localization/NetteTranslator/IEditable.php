<?php

namespace NetteTranslator;

/**
 * @author Jan Smitka <jan@smitka.org>
 * @author Vaclav Vrbka <gmvasek@php-info.cz>
 */
interface IEditable extends \Nette\Localization\ITranslator
{
	public function getVariantsCount();
	public function getStrings($file = NULL);
	public function setTranslation($message, $string, $file);
	public function save($file);
}