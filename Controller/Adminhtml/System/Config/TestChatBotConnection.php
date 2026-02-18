<?php
namespace ClusterifyAI\Chatbot\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use ClusterifyAI\Chatbot\Api\AuthTokenProviderInterface;

class TestChatBotConnection extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var AuthTokenProviderInterface
     */
    protected $authTokenProvider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AuthTokenProviderInterface $authTokenProvider
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AuthTokenProviderInterface $authTokenProvider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authTokenProvider = $authTokenProvider;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Check ChatBot Connection (Widget, Auth, Status)
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        $baseUrl = $this->getRequest()->getParam('api_base_url');
        if (!$baseUrl) {
            $baseUrl = $this->scopeConfig->getValue(\ClusterifyAI\Chatbot\Model\AuthTokenProvider::XML_PATH_API_BASE_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $loginPath = $this->getRequest()->getParam('api_login_path');
        if (!$loginPath) {
            $loginPath = $this->scopeConfig->getValue(\ClusterifyAI\Chatbot\Model\AuthTokenProvider::XML_PATH_API_LOGIN_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $publicKey = $this->getRequest()->getParam('public_key');
        if (!$publicKey) {
             $publicKey = $this->scopeConfig->getValue(\ClusterifyAI\Chatbot\Model\AuthTokenProvider::XML_PATH_PUBLIC_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        $secretKey = $this->getRequest()->getParam('secret_key');
        if (!$secretKey) {
             $encryptedSecret = $this->scopeConfig->getValue(\ClusterifyAI\Chatbot\Model\AuthTokenProvider::XML_PATH_SECRET_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
             if ($encryptedSecret) {
                $secretKey = $this->encryptor->decrypt($encryptedSecret);
             }
        }
        
        // Optional params from ChatBot form
        $loaderPath = $this->getRequest()->getParam('loader_js_path');
        $statusCheckPath = $this->getRequest()->getParam('status_check_path');
        $uuid = $this->getRequest()->getParam('uuid');

        if (!$baseUrl || !$loginPath || !$publicKey || !$secretKey) {
             return $result->setData([
                'success' => false,
                'message' => __('Please configure API Access first (Base URL, Login Path, Public Key, Secret Key).')
            ]);
        }

        try {
            $connectionResult = $this->authTokenProvider->testFullConnection(
                $baseUrl,
                $loginPath,
                $publicKey,
                $secretKey,
                $loaderPath,
                $statusCheckPath,
                $uuid
            );
            return $result->setData($connectionResult);
        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ClusterifyAI_Chatbot::config');
    }
}
