<?php
namespace ClusterifyAI\Chatbot\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use ClusterifyAI\Chatbot\Api\AuthTokenProviderInterface;

class TestApiLogin extends Action
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
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param AuthTokenProviderInterface $authTokenProvider
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        AuthTokenProviderInterface $authTokenProvider
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authTokenProvider = $authTokenProvider;
    }

    /**
     * Check API Connection
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        
        $baseUrl = $this->getRequest()->getParam('api_base_url');
        $loginPath = $this->getRequest()->getParam('api_login_path');
        $publicKey = $this->getRequest()->getParam('public_key');
        $secretKey = $this->getRequest()->getParam('secret_key');

        if (!$baseUrl || !$loginPath || !$publicKey || !$secretKey) {
             return $result->setData([
                'success' => false,
                'message' => __('Please fill all required fields (Base URL, Login Path, Public Key, Secret Key).')
            ]);
        }

        try {
            $connectionResult = $this->authTokenProvider->testConnection(
                $baseUrl,
                $loginPath,
                $publicKey,
                $secretKey
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
