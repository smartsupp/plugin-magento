<?php

namespace Smartsupp\Smartsupp\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Smartsupp\Smartsupp\Helper\Data;

/**
 * Class Uninstall
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Uninstall constructor.
     * @param Data $dataHelper
     */
    public function __construct(Data $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Run uninstall data.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $configMapping = array('active', 'chatId', 'email', 'optionalCode');

        foreach ($configMapping as $name) {
            $this->dataHelper->deleteGeneralConfig($name);
        }

        $setup->endSetup();
    }
}
