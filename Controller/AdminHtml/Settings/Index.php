<?php

namespace Smartsupp\Smartsupp\Controller\Adminhtml\Settings;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Smartsupp\Auth\Api;
use Smartsupp\Smartsupp\Block\Adminhtml\SmartsuppBlock;

require_once __DIR__ . '/../../../Auth/Api.php';
require_once __DIR__ . '/../../../Auth/Request/HttpRequest.php';
require_once __DIR__ . '/../../../Auth/Request/CurlRequest.php';

/**
 * Index Controller Class.
 *
 * @category Class
 * @package  Smartsupp
 * @author   Smartsupp <vladimir@smartsupp.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.smartsupp.com
 */
class Index extends Action
{

	const CONFIG_PATH = __DIR__ . '/../../../etc/config.json';

	/** @var PageFactory */
	private $pageFactory;


	// @todo seems to be unused - remove pageFactory

	/**
	 * @param Context $context
	 */
	public function __construct(Context $context)
	{
		parent::__construct($context);
		$this->pageFactory = $this->_objectManager->create('Magento\Framework\View\Result\PageFactory');
	}


	public function execute()
	{
		$formAction = $message = $email = NULL;

		if (isset($_GET['ssaction'])) {
			$action = (string) $_GET['ssaction'];
			switch ($action) {
				case 'login':
				case 'register':
					$formAction = $action;
					$api = new Api;
					$data = array(
						'email' => $_POST['email'],
						'password' => $_POST['password'],
					);
					try {
						$response = $formAction === 'login' ? $api->login($data) : $api->create($data + array(/*'partnerKey' => 'k717wrqdi5', */'lang' => 'en'));

						if (isset($response['error'])) {
							$message = $response['message'];
							$email = $_POST['email'];
						} else {
							$this->activate($response['account']['key'], $_POST['email']);
						}
					} catch (Exception $e) {
						$message = $e->getMessage();
						$email = $_POST['email'];
					}
					break;
				case 'update':
					$message = 'Custom code was updated.';
					$this->updateOptions(array(
						'optional-code' => $_POST['code'],
					));
					break;
				case 'disable':
					$this->deactivate();
					break;
				default:
					$message = 'Invalid action';
					break;
			}
		}

		$config = @file_get_contents(self::CONFIG_PATH);
		if ($config === FALSE) {
			$config = ['active' => FALSE];
		} else {
			$config = json_decode($config, JSON_OBJECT_AS_ARRAY);
		}
		SmartsuppBlock::setVars($formAction, $message, $email, $config);

		$this->_view->loadLayout();
		$this->_view->renderLayout();
	}


	private function activate($chatId, $email)
	{
		$this->updateOptions(array(
			'active' => TRUE,
			'chat-id' => (string) $chatId,
			'email' => (string) $email,
		));
	}


	private function deactivate()
	{
		$this->updateOptions(array(
			'active' => FALSE,
			'chat-id' => NULL,
			'email' => NULL
		));
	}


	private function updateOptions(array $options)
	{
		$config = @file_get_contents(self::CONFIG_PATH);
		if ($config === FALSE) {
			$config = '{}';
		}
		$config = json_decode($config);
		foreach ($options as $key => $value) {
			$config->$key = $value;
		}
		file_put_contents(self::CONFIG_PATH, json_encode($config));
	}

}
