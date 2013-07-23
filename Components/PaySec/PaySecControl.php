<?php

namespace Schmutzka\Controls;

use Nette;
use Schmutzka;
use Schmutzka\Application\UI\Control;
use Schmutzka\Forms\Form;

class PaySecControl extends Control
{
	/** @var string */
	public $message;

	/** @var string */
	public $awayRedirect = 'Homepage:default';

	/** @var callback to external action */
	protected $onSuccessTransaction;

	/** @var array */
	private $settings;

	/** @var Schmutzka\Shop\Basket */
	private $basket;

	/** @var Schmutzka\Models\* */
	private $orderModel;


	/**
	 * @param Schmutzka\ParamService
	 * @param Schmutzka\Shop\Basket
	 */
	function inject(Schmutzka\ParamService $paramService, Schmutzka\Shop\Basket $basket)
	{
		$settings = $paramService->params['paysec'];
		$this->settings = $settings[$settings['use']];
		$this->basket = $basket;
	}


	protected function createComponentForm(/*$name*/) // @intentionaly: required for custom action
	{
		$paymentId = $this->basket->paySecId;

		// test!
		$backUrl = $this->link('//process', array(
			'id' => $paymentId
		)) . '&result={0}';  // {0} will be replaced by result state

		$cancelUrl = $this->link('//cancel', array(
			'id' => $paymentId
		));

		$form = new Form/*($this, $name)*/; // @intentionaly: required for custom action
		// $form->setAction($this->settings['gateway']);

		$form->addHidden('MicroaccountNumber', $this->settings['accountNumber']);
		$form->addHidden('Amount', $this->basket->priceTotal);
		$form->addHidden('MerchantOrderId', $paymentId);
		$form->addHidden('MessageForTarget', $this->message);
		$form->addHidden('BackURL', $backUrl);
		$form->addHidden('CancelURL', $cancelUrl);

		$form->addSubmit('pay', 'Zaplatit')
			->setAttribute('class', 'btn btn-large btn-success')
			->onClick[] = callback($this, 'processConfirm');

		$form->addSubmit('cancel', 'Zrušit objednávku')
			->setAttribute('class', 'btn btn-large')
			->onClick[] = callback($this, 'processCancel');

		return $form;
	}


	/**
	 * Confirm payment
	 * @param Nette\Forms\Controls\SubmitButton
	 */
	public function processConfirm(Nette\Forms\Controls\SubmitButton $button)
	{
		$values = $button->form->values;

		$url = $this->settings['gateway'] . '?' . http_build_query($values);
		$this->presenter->redirectUrl($url);
	}


	/**
	 * Cancel order via form
	 * @param Nette\Forms\Controls\SubmitButton
	 */
	public function processCancel(Nette\Forms\Controls\SubmitButton $button)
	{
		$this->cancelOrder($this->basket->paySecId);
	}


	/********************** return actions **********************/


	/**
	 * Process transaction result
	 * @param int
	 * @param int
	 */
	public function handleProcess($id, $result)
	{
		$userName = $this->settings['userName'];
		$password = $this->settings['password'];

		$this->orderModel->update(array(
			'result' => $result
		), $id);

		if ($orderRow = $this->orderModel->item($id)) { // hm?
			$paysecMapi = new SoapClient($this->settings['soap']);
			$resultCode = $paysecMapi->VerifyTransactionIsPaid($this->settings['userName'], $this->settings['password'], $id, $orderRow['price']);

			switch ($resultCode) {

				case 0:
					$this->orderModel->update(array('paid' => 1), $id);

					$this->basket->emptyBasket();
					$this->presenter->flashMessage('Platba prostřednictvím systému PaySec proběhla úspěšně. Děkujeme za Vaši objednávku a zájem o festival Jeden svět. Kód vstupenky najdete ve Vaší emalové schránce.', 'success');

					if ($this->onSuccessTransaction && is_callable($this->onSuccessTransaction)) {
						$values = call_user_func($this->onSuccessTransaction, $values);
					}


					$this->presenter->redirect($this->awayRedirect);
					break;

				case 1:
					$this->presenter->flashMessage('Platbu se nepodařilo zrealizovat.<br>Váš pokyn k zamítnutí platby byl proveden úspěšně.', 'success');
					break;

				case 2:
					$this->presenter->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.2', 'error');
					break;

				case 3:
					$this->presenter->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.3', 'error');
					break;

				case 4:
					$this->presenter->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.4', 'error');
					break;

				case 5:
					$this->presenter->flashMessage('Platbu se nepodařilo zrealizovat.', 'error');
					break;

				case 6:
					$this->presenter->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.6', 'error');
					break;

				case 7:
					$this->presenter->flashMessage('');
					break;

				default:
					$this->presenter->flashMessage('Stav platby se nepodařilo ověřit. Pracujeme na nápravě.', 'error');
					break;
				}
		}
	}


	/**
	 * Cancel order via url
	 * @param int
	 */
	public function handleCancel($id)
	{
		$this->cancelOrder($id);
	}


	/********************** helpers **********************/


	/**
	 * Cancel order
	 * @param int
	 */
	private function cancelOrder($id)
	{
		$this->orderModel->cancelOrder($id);
		$this->presenter->flashMessage('Váš pokyn k zamítnutí platby byl proveden úspěšně. Pokud budete chtít objednat vstupenky, vložte je znovu do košíku.', 'success');
		$this->basket->emptyBasket();
		$this->presenter->redirect($this->awayRedirect);
	}


	/**
	 * Set order model
	 * @param Schmutzka\Models\*
	 */
	public function setOrderModel($orderModel)
	{
		$this->orderModel = $orderModel;
	}


	/**
     * @param callable
     * @return self
     */
    public function setOnSuccessTransaction($callback)
    {
        $this->onSuccessTransaction = $callback;
        return $this;
    }

}
