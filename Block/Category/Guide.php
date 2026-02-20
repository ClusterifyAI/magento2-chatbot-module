<?php
declare(strict_types=1);

namespace ClusterifyAI\Chatbot\Block\Category;

use Magento\Catalog\Block\Category\View;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Guide extends View
{
    private const XML_PATH_ENABLE_GUIDE = 'clusterifyai_chatbot/guide_attributes/enable_guide_categories';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        Resolver $layerResolver,
        Registry $registry,
        CategoryHelper $categoryHelper,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }

    /**
     * Check if the guide attribute feature is enabled for categories in configuration
     *
     * @return bool
     */
    public function isFeatureEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_GUIDE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get the value of the clusterify_chatbot_guide attribute for the current category
     *
     * @return string|null
     */
    public function getGuideAttributeValue(): ?string
    {
        $category = $this->getCurrentCategory();
        if (!$category) {
            return null;
        }

        $value = $category->getData('clusterify_chatbot_guide');
        return is_string($value) ? trim($value) : null;
    }
}
