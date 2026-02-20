<?php
declare(strict_types=1);

namespace ClusterifyAI\Chatbot\Block\Cms;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

class Guide extends Template
{
    private const XML_PATH_ENABLE_GUIDE = 'clusterifyai_chatbot/guide_attributes/enable_guide_cms';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Page
     */
    private $page;

    public function __construct(
        Context $context,
        Page $page,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->page = $page;
        parent::__construct($context, $data);
    }

    /**
     * Check if the guide attribute feature is enabled for CMS pages in configuration
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
     * Get the value of the clusterify_chatbot_guide attribute for the current CMS page
     *
     * @return string|null
     */
    public function getGuideAttributeValue(): ?string
    {
        $value = $this->page->getData('clusterify_chatbot_guide');
        return is_string($value) ? trim($value) : null;
    }
}
