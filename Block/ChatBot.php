<?php
namespace ClusterifyAI\Chatbot\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ChatBot extends Template
{
    const XML_PATH_ENABLED = 'clusterifyai_chatbot/base/enabled';
    const XML_PATH_API_BASE_URL = 'clusterifyai_api/general/api_base_url';
    const XML_PATH_LOADER_JS_PATH = 'clusterifyai_chatbot/connection/loader_js_path';
    const XML_PATH_STATUS_CHECK_PATH = 'clusterifyai_chatbot/connection/status_check_path';
    const XML_PATH_UUID = 'clusterifyai_chatbot/connection/uuid';

    const XML_PATH_SHOW_HOME = 'clusterifyai_chatbot/display_settings/show_on_home';
    const XML_PATH_SHOW_CMS = 'clusterifyai_chatbot/display_settings/show_on_cms';
    const XML_PATH_SHOW_CATEGORY = 'clusterifyai_chatbot/display_settings/show_on_category';
    const XML_PATH_SHOW_PRODUCT = 'clusterifyai_chatbot/display_settings/show_on_product';
    const XML_PATH_SHOW_CHECKOUT = 'clusterifyai_chatbot/display_settings/show_on_checkout';
    const XML_PATH_SHOW_ACCOUNT = 'clusterifyai_chatbot/display_settings/show_on_account';

    const XML_PATH_SHOW_SEARCH = 'clusterifyai_chatbot/display_settings/show_on_search';
    const XML_PATH_SHOW_CART = 'clusterifyai_chatbot/display_settings/show_on_cart';
    const XML_PATH_SHOW_CONTACT = 'clusterifyai_chatbot/display_settings/show_on_contact';
    const XML_PATH_SHOW_LOGIN = 'clusterifyai_chatbot/display_settings/show_on_login';
    const XML_PATH_SHOW_REGISTER = 'clusterifyai_chatbot/display_settings/show_on_register';
    const XML_PATH_SHOW_FORGOTPASSWORD = 'clusterifyai_chatbot/display_settings/show_on_forgotpassword';
    const XML_PATH_SHOW_ADVANCED_SEARCH = 'clusterifyai_chatbot/display_settings/show_on_advanced_search';
    const XML_PATH_SHOW_COMPARE = 'clusterifyai_chatbot/display_settings/show_on_compare';
    const XML_PATH_SHOW_WISHLIST = 'clusterifyai_chatbot/display_settings/show_on_wishlist';
    const XML_PATH_SHOW_OTHER = 'clusterifyai_chatbot/display_settings/show_on_other';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->request = $request;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function isEnabled()
    {
        if (!$this->_scopeConfig->isSetFlag(self::XML_PATH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
            return false;
        }

        return $this->canShowOnCurrentPage();
    }

    public function canShowOnCurrentPage()
    {
        $fullActionName = $this->request->getFullActionName();

        // 1. Home Page
        if ($fullActionName == 'cms_index_index') {
            return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_HOME, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 2. CMS Pages (excluding Home)
        if ($fullActionName == 'cms_page_view') {
            return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_CMS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 3. Category Pages
        if ($fullActionName == 'catalog_category_view') {
            return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_CATEGORY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 4. Product Pages
        if ($fullActionName == 'catalog_product_view') {
            return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_PRODUCT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 5. Checkout Pages
        if (strpos($fullActionName, 'checkout') !== false) {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_CHECKOUT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 6. My Account Pages
        if (strpos($fullActionName, 'customer_account') !== false && $fullActionName !== 'customer_account_login' && $fullActionName !== 'customer_account_create' && $fullActionName !== 'customer_account_forgotpassword') {
            return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_ACCOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 7. Search Results
        if ($fullActionName == 'catalogsearch_result_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_SEARCH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 8. Shopping Cart
        if ($fullActionName == 'checkout_cart_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_CART, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 9. Contact Us
        if ($fullActionName == 'contact_index_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_CONTACT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 10. Login Page
        if ($fullActionName == 'customer_account_login') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_LOGIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 11. Registration Page
        if ($fullActionName == 'customer_account_create') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_REGISTER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 12. Forgot Password
        if ($fullActionName == 'customer_account_forgotpassword') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_FORGOTPASSWORD, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 13. Advanced Search
        if ($fullActionName == 'catalogsearch_advanced_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_ADVANCED_SEARCH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 14. Compare Products
        if ($fullActionName == 'catalog_product_compare_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_COMPARE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // 15. Wishlist
        if ($fullActionName == 'wishlist_index_index') {
             return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_WISHLIST, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        // Fallback for Other Pages
        return $this->_scopeConfig->isSetFlag(self::XML_PATH_SHOW_OTHER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getLoaderJsUrl()
    {
        $baseUrl = $this->_scopeConfig->getValue(self::XML_PATH_API_BASE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $path = $this->_scopeConfig->getValue(self::XML_PATH_LOADER_JS_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function getStatusCheckUrl()
    {
        $baseUrl = $this->_scopeConfig->getValue(self::XML_PATH_API_BASE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $path = $this->_scopeConfig->getValue(self::XML_PATH_STATUS_CHECK_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    public function getUuid()
    {
        return $this->_scopeConfig->getValue(self::XML_PATH_UUID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}

