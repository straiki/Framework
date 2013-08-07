<?php

namespace Schmutzka\Forms\Controls;

use Nette\Forms\Controls\TextInput,
	Nette\Forms\Form,
	Nette\Utils\Html;

/**
 * @author  Michal Mikoláš <nanuqcz@gmail.com>
 */
class AntispamControl extends TextInput
{

	/** @var int  minimum delay [sec] to send form */
	public static $minDelay = 5;


	/**
	 * Register Antispam to Nette Forms
	 * @return void
	 */
	public static function register()
	{
		Form::extensionMethod('addAntispam', function(Form $form, $name = 'spam', $label = 'Toto pole vymažte', $msg = 'Byl detekován pokus o spam.'){
			// 'All filled' protection
			$form[$name] = new AntispamControl($label, NULL, NULL, $msg);

			// 'Send delay' protection
			$form->addHidden('form_created', strtr(time(), '0123456789', 'jihgfedcba'))
				->addRule(
					function($item){
						if (AntispamControl::$minDelay <= 0) return TRUE;  // turn off 'Send delay protection'

						$value = (int) strtr($item->value, 'jihgfedcba', '0123456789');

						$timeDiff = time() - $value;
						$check = $timeDiff >= AntispamControl::$minDelay;

						// loging
						if (!$check) {
							file_put_contents(WWW_DIR . '/antispam.log', 'Fill time: ' . $timeDiff . ' s \n', FILE_APPEND);
                        }

						return $check;
					},
					$msg
				);

			return $form;
		});
	}


	/**
	 * @param string|Html
	 * @param int
	 * @param int
	 * @param string
	 */
	public function __construct($label = '', $cols = NULL, $maxLength = NULL, $msg = '')
	{
		parent::__construct($label, $cols, $maxLength);

		$this->setDefaultValue('http://');
		$this->addRule(~Form::FILLED, $msg);
	}


	/**
	 * @return TextInput
	 */
	public function getControl()
	{
		$control = parent::getControl();

		$control = $this->addAntispamScript($control);
		return $control;
	}


	/**
	 * @param Html
	 * @return Html
	 */
	protected function addAntispamScript(Html $control)
	{
		$control = Html::el('')->add($control);
		$control->add( Html::el('script', array('type' => 'text/javascript'))->setHtml('
				// Clear input value
				var input = document.getElementById('' . $control[0]->id . '');
				input.value = '';

				// Hide input and label
				if (input.parentNode.parentNode.nodeName == 'TR') {
					// DefaultFormRenderer
					input.parentNode.parentNode.style.display = 'none';
				} else {
					// Manual render
					input.style.display = 'none';
					var labels = input.parentNode.getElementsByTagName('label');
					for (var i = 0; i < labels.length; i++) {  // find and hide label
						if (labels[i].getAttribute('for') == '' . $control[0]->id . '') {
							labels[i].style.display = 'none';
						}
					}
				}
			')
		);

		return $control;
	}

}