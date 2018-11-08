<?php

namespace Smartsupp\Smartsupp\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Smartsupp\Smartsupp\Helper\Data;

/**
 * Data migration:
 * - Config Migration from version 2.0.1 JSON file store to 2.0.2+ Magento config way
 *
 */
class UpgradeData implements  UpgradeDataInterface
{
    const CONFIG_PATH = '/../etc/config.json';

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * UpgradeData constructor.
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Run data upgrade.
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        // need to migrate old JSON config
        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            // migrate config
            $this->migrateConfig();

            // remove old json file
            if(file_exists($this->getConfigPath())) {
                unlink($this->getConfigPath());
            }
        }

        $setup->endSetup();
    }

    /**
     * Will load old JSON config file (if any) and transfer its data into Magento Smartsupp plugin config
     */
    private function migrateConfig()
    {
        // no config file, skip
        if(!file_exists($this->getConfigPath())) {
            return;
        }

        $configJson = file_get_contents($this->getConfigPath());
        $configData = json_decode($configJson, true);

        $configMapping = array(
            'active' => 'active',
            'chat-id' => 'chatId',
            'email' => 'email',
            'optional-code' => 'optionalCode',
        );

        foreach ($configMapping as $oldName => $newName) {
            // put into new config file - native Magento one
            $this->dataHelper->setGeneralConfig($newName, $configData[$oldName]);
        }
    }

    /**
     * @return string full JSON config path
     */
    private function getConfigPath()
    {
        return __DIR__ . self::CONFIG_PATH;
    }
}
