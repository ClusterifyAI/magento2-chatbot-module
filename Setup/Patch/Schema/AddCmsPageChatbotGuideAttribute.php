<?php
declare(strict_types=1);

namespace ClusterifyAI\Chatbot\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddCmsPageChatbotGuideAttribute implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();

        $connection = $this->schemaSetup->getConnection();
        $tableName = $this->schemaSetup->getTable('cms_page');

        if ($connection->isTableExists($tableName) && !$connection->tableColumnExists($tableName, 'clusterify_chatbot_guide')) {
            $connection->addColumn(
                $tableName,
                'clusterify_chatbot_guide',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Clusterify Chatbot Guide Information'
                ]
            );
        }

        $this->schemaSetup->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
