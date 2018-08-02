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
    const DOMAIN = 'smartsupp';

	const CONFIG_PATH = __DIR__ . '/../../../etc/config.json';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context     $context           context
     * @param PageFactory $resultPageFactory page factory
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

	public function execute()
	{
		$formAction = $message = $email = NULL;

        $slaction = $this->getRequest()->getParam('slaction');
        $email = $this->getRequest()->getParam('email');
        $password = $this->getRequest()->getParam('password');
        $code = $this->getRequest()->getParam('code');

		if (isset($slaction)) {
			switch ($slaction) {
				case 'login':
				case 'register':
					$formAction = $slaction;
					$api = new Api;
					$data = array(
						'email' => $email,
						'password' => $password,
					);
					try {
						$response = $formAction === 'login' ? $api->login($data) : $api->create($data + array(/*'partnerKey' => 'k717wrqdi5', */'lang' => 'en'));

						if (isset($response['error'])) {
							$message = $response['message'];
						} else {
							$this->activate($response['account']['key'], $email);
						}
					} catch (Exception $e) {
						$message = $e->getMessage();
					}
					break;
				case 'update':
					$message = 'Custom code was updated.';
					$this->updateOptions(array(
						'optional-code' => $code,
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

        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('smartsupp.settings');
        if ($block) {
            $block->setDomain(self::DOMAIN);
            $block->setFormAction($formAction);
            $block->setMessage($message);
            $block->setEmail($email ?: $this->_getOption('email'));
            $block->setEnabled((bool) $this->_getOption('email', null));
            $block->setOptionalCode($code ?: $this->_getOption('optional-code'));
        }
        return $resultPage;
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


    /**
     * Get options from file
     *
     * @return array
     */
    private function _getOptions()
    {
        $config = @file_get_contents(self::CONFIG_PATH);
        if (!$config) {
            $config = ['active' => FALSE];
        } else {
            $config = json_decode($config, JSON_OBJECT_AS_ARRAY);
        }
        return $config;
    }


    /**
     * Get option from file
     *
     * @param String $name    option name
     * @param String $default default value
     *
     * @return String
     */
    private function _getOption($name, $default = null)
    {
        $options = $this->_getOptions();
        return isset($options[$name]) ? $options[$name] : $default;
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
