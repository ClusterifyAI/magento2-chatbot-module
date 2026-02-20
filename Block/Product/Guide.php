<?php
declare(strict_types=1);

namespace ClusterifyAI\Chatbot\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Guide extends AbstractProduct
{
    private const XML_PATH_ENABLE_GUIDE = 'clusterifyai_chatbot/guide_attributes/enable_guide_products';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * Check if the guide attribute feature is enabled in configuration
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
     * Get the value of the clusterify_chatbot_guide attribute for the current product
     *
     * @return string|null
     */
    public function getGuideAttributeValue(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        $value = $product->getData('clusterify_chatbot_guide');
        return is_string($value) ? trim($value) : null;
    }
}
