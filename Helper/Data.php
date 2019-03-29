<?php

namespace Smartsupp\Smartsupp\Helper;

use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Data Helper.
 *
 * @category Class
 * @package  Smartsupp
 * @author   Smartsupp <vladimir@smartsupp.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.smartsupp.com
 */
class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $resourceConfig;

    /**
     * @var array simple array to store set values, then there is no need to clear cache during each config entry set
     */
    protected $cacheValues;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    const XML_PATH = 'smartsupp/chat/';

    /**
     * Data constructor.
     * @param Context $context
     * @param Config $resourceConfig
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        Config $resourceConfig,
        TypeListInterface $cacheTypeList
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->cacheValues = [];
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context);
    }

    /**
     * Get config value. Use helper config cache if needed to remove the need to refresh the config cache every time
     * some config entry is updated.
     *
     * @param string $field full field path
     * @param int|null $storeId store id
     * @return mixed config entry value
     */
    private function getConfigValue($field, $storeId = null)
    {
        // if have it locally, return. Do not want to flush Magento cache every time config entry is updated = slow.
        if (isset($this->cacheValues[$field])) {
            return $this->cacheValues[$field];
        } else {
            return $this->scopeConfig->getValue(
                $field,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    /**
     * Will return the config entry value by its last path identifier.
     *
     * @param string $code field name (not a full one)
     * @param int|null $storeId store id
     * @return mixed config entry value
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH . $code, $storeId);
    }

    /**
     * Set config value. Store it into helper cache to make things faster.
     *
     * @param string $field field name
     * @param string $value field value to be set
     */
    private function setConfigValue($field, $value)
    {
        $this->resourceConfig->saveConfig(
            $field,
            $value,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );

        // store in helper cache
        $this->cacheValues[$field] = $value;
    }

    /**
     * Will set the config entry value by its last path identifier.
     *
     * @param string $code field name (not a full one)
     * @param string $value field value to be set
     */
    public function setGeneralConfig($code, $value)
    {
        return $this->setConfigValue(self::XML_PATH . $code, $value);
    }

    /**
     * Will remove the config entry value by its last path identifier.
     *
     * @param $code field name (not a full one)
     */
    public function deleteGeneralConfig($code)
    {
        $this->deleteConfigValue(self::XML_PATH . $code);
    }

    /**
     * Clear general config.
     * @see https://magento.stackexchange.com/questions/92917/magento-2-programmatically-add-a-value-to-core-config-data
     */
    public function clearGeneralConfigCache()
    {
        $this->cacheTypeList->cleanType(CacheConfig::TYPE_IDENTIFIER);
    }

    /**
     * Will remove the config entry
     *
     * @param $field field name
     */
    private function deleteConfigValue($field)
    {
        $this->resourceConfig->deleteConfig(
            $field,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId = 0
        );

        // unset in helper cache
        unset($this->cacheValues[$field]);
    }
}
