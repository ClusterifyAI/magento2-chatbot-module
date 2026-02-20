<?php
declare(strict_types=1);

namespace ClusterifyAI\Chatbot\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class UpdateChatbotGuideAttributeGroup implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        
        $groupName = 'Clusterify.AI - ChatBot Guide';

        // Ensure the group exists
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 150);
        $attributeGroupId = $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        // Move the attribute to the new group
        $eavSetup->addAttributeToGroup(
            $entityTypeId,
            $attributeSetId,
            $attributeGroupId,
            'clusterify_chatbot_guide',
            10
        );

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [
            \ClusterifyAI\Chatbot\Setup\Patch\Data\AddChatbotGuideAttribute::class
        ];
    }

    public function getAliases()
    {
        return [];
    }
}
