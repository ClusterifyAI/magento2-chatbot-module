<?php
namespace ClusterifyAI\Chatbot\Model;

use ClusterifyAI\Chatbot\Api\AuthTokenProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\ScopeInterface;

class AuthTokenProvider implements AuthTokenProviderInterface
{
    const XML_PATH_API_BASE_URL = 'clusterifyai_api/general/api_base_url';
    const XML_PATH_API_LOGIN_PATH = 'clusterifyai_api/general/api_login_path';
    const XML_PATH_PUBLIC_KEY = 'clusterifyai_api/general/public_key';
    const XML_PATH_SECRET_KEY = 'clusterifyai_api/general/secret_key';
    const XML_PATH_TOKEN_EXPIRY = 'clusterifyai_api/general/token_expiry';

    const CACHE_TAG = 'CLUSTERIFYAI_API_TOKEN';
    const CACHE_KEY_PREFIX = 'clusterifyai_api_token_';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CurlFactory
     */
    protected $curlFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EncryptorInterface $encryptor
     * @param CurlFactory $curlFactory
     * @param Json $json
     * @param CacheInterface $cache
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor,
        CurlFactory $curlFactory,
        Json $json,
        CacheInterface $cache
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function getToken($publicKey = null, $secretKey = null)
    {
        if (!$publicKey) {
            $publicKey = $this->scopeConfig->getValue(self::XML_PATH_PUBLIC_KEY, ScopeInterface::SCOPE_STORE);
        }
        
        if (!$secretKey) {
            $secretKey = $this->scopeConfig->getValue(self::XML_PATH_SECRET_KEY, ScopeInterface::SCOPE_STORE);
            $secretKey = $this->encryptor->decrypt($secretKey);
        }

        if (!$publicKey || !$secretKey) {
            return '';
        }

        $cacheKey = self::CACHE_KEY_PREFIX . md5($publicKey);
        $cachedToken = $this->cache->load($cacheKey);

        if ($cachedToken) {
            return $cachedToken;
        }

        $baseUrl = $this->scopeConfig->getValue(self::XML_PATH_API_BASE_URL, ScopeInterface::SCOPE_STORE);
        $loginPath = $this->scopeConfig->getValue(self::XML_PATH_API_LOGIN_PATH, ScopeInterface::SCOPE_STORE);
        
        $url = rtrim($baseUrl, '/') . '/' . ltrim($loginPath, '/');

        $result = $this->performLogin($url, $publicKey, $secretKey);

        if ($result['success'] && !empty($result['token'])) {
            $expiryMinutes = (int)$this->scopeConfig->getValue(self::XML_PATH_TOKEN_EXPIRY, ScopeInterface::SCOPE_STORE);
            $expirySeconds = $expiryMinutes * 60;
            $this->cache->save($result['token'], $cacheKey, [self::CACHE_TAG], $expirySeconds);
            return $result['token'];
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function testConnection($baseUrl, $loginPath, $publicKey, $secretKey)
    {
        // This method is kept for backward compatibility if needed, or simple auth check
        return $this->performLoginWithCredentials($baseUrl, $loginPath, $publicKey, $secretKey);
    }

    /**
     * @inheritdoc
     */
    public function testFullConnection($baseUrl, $loginPath, $publicKey, $secretKey, $loaderPath = null, $statusCheckPath = null, $uuid = null)
    {
        $result = [
            'success' => false,
            'widget_status' => 'Pending',
            'auth_status' => 'Pending',
            'chatbot_status' => 'Pending',
            'message' => ''
        ];

        // Ensure clean inputs
        $baseUrl = rtrim(trim($baseUrl), '/');
        $loginPath = ltrim(trim($loginPath), '/');

        // 1. Check Widget Load
        $loaderPath = $loaderPath 
            ? ltrim(trim($loaderPath), '/') 
            : $this->scopeConfig->getValue('clusterifyai_chatbot/connection/loader_js_path', ScopeInterface::SCOPE_STORE);
            
        if (!$loaderPath) {
             $loaderPath = 'static/clusterify-chatbot.bundle.min.js';
        }
        $loaderPath = ltrim(trim($loaderPath), '/');

        $loaderUrl = $baseUrl . '/' . $loaderPath;
        
        try {
            /** @var \Magento\Framework\HTTP\Client\Curl $curl */
            $curl = $this->curlFactory->create();
            $curl->setOption(CURLOPT_NOBODY, true);
            $curl->get($loaderUrl);
            $code = $curl->getStatus();
            
            if ($code >= 200 && $code < 400) {
                $result['widget_status'] = 'OK';
            } else {
                $result['widget_status'] = 'Failed (' . $code . ')';
                $result['message'] .= "Widget Load Failed ($code). ";
            }
        } catch (\Exception $e) {
            $result['widget_status'] = 'Error';
            $result['message'] .= "Widget Load Error: " . $e->getMessage() . ". ";
        }

        // 2. Auth Token (Login)
        $loginResult = $this->performLoginWithCredentials($baseUrl, $loginPath, $publicKey, $secretKey);
        
        if ($loginResult['success']) {
            $result['auth_status'] = 'Enabled';
            $token = $loginResult['token'];
        } else {
            $result['auth_status'] = 'Failed';
            $result['message'] .= "Login Failed: " . $loginResult['message'] . ". ";
            // Cannot proceed without token
            return $result; 
        }

        // 3. ChatBot Status Check
        $statusCheckPath = $statusCheckPath 
            ? ltrim(trim($statusCheckPath), '/') 
            : $this->scopeConfig->getValue('clusterifyai_chatbot/connection/status_check_path', ScopeInterface::SCOPE_STORE);

        $uuid = $uuid ? trim($uuid) : $this->scopeConfig->getValue('clusterifyai_chatbot/connection/uuid', ScopeInterface::SCOPE_STORE);

        if (!$statusCheckPath) {
             $statusCheckPath = 'v1/chatbot_status';
        }

        if (!$uuid) {
             $result['chatbot_status'] = 'Skipped';
             $result['message'] .= "UUID not configured.";
             $result['success'] = true; // Partial success logic
             return $result;
        }

        $statusUrl = $baseUrl . '/' . $statusCheckPath;

        try {
            /** @var \Magento\Framework\HTTP\Client\Curl $curl */
            $curl = $this->curlFactory->create();
            
            $payload = $this->json->serialize(['public_uuid' => $uuid]);
            
            $curl->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ]);

            $curl->post($statusUrl, $payload);
            
            $statusCode = $curl->getStatus();
            $body = $curl->getBody();

            if ($statusCode >= 200 && $statusCode < 300) {
                try {
                    $statusData = $this->json->unserialize($body);
                    $chatbotStatus = isset($statusData['chatbot_status']) ? $statusData['chatbot_status'] : 'Enabled';
                    $result['chatbot_status'] = $chatbotStatus;
                    $result['success'] = true;
                } catch (\Exception $e) {
                    $result['chatbot_status'] = 'OK'; // Assume OK if 200 but parse fails? Or Warn?
                    $result['success'] = true;
                    $result['message'] .= "Invalid JSON response from Status API.";
                }
            } else {
                // Parse error details if available
                $errorMsg = "HTTP $statusCode";
                try {
                    $errorData = $this->json->unserialize($body);
                    if (isset($errorData['message'])) {
                        $errorMsg = $errorData['message'];
                    } elseif (isset($errorData['detail'])) {
                        $errorMsg = $errorData['detail'];
                    }
                } catch (\Exception $e) {
                    // Ignore body parse error on failure
                }
                
                $result['chatbot_status'] = 'Failed (' . $statusCode . ')';
                $result['message'] .= "ChatBot Status Failed: " . $errorMsg;
            }

        } catch (\Exception $e) {
            $result['chatbot_status'] = 'Error';
            $result['message'] .= "Status Check Error: " . $e->getMessage();
        }

        return $result;
    }

    private function performLoginWithCredentials($baseUrl, $loginPath, $publicKey, $secretKey) {
        $url = rtrim(trim($baseUrl), '/') . '/' . ltrim(trim($loginPath), '/');
        
        if (preg_match('/^\*+$/', $secretKey)) {
             $savedSecret = $this->scopeConfig->getValue(self::XML_PATH_SECRET_KEY, ScopeInterface::SCOPE_STORE);
             $secretKey = $this->encryptor->decrypt($savedSecret);
        }

        return $this->performLogin($url, $publicKey, $secretKey);
    }

    /**
     * Perform the actual login request
     *
     * @param string $url
     * @param string $username
     * @param string $password
     * @return array
     */
    private function performLogin($url, $username, $password)
    {
        try {
            /** @var \Magento\Framework\HTTP\Client\Curl $curl */
            $curl = $this->curlFactory->create();

            // OAuth2PasswordRequestForm requires form data
            $payload = [
                'username' => $username,
                'password' => $password
            ];

            // Use http_build_query for x-www-form-urlencoded
            $postData = http_build_query($payload);
            
            // Force options to ensure body is sent
            $curl->setOption(CURLOPT_POST, true);
            $curl->setOption(CURLOPT_POSTFIELDS, $postData);
            $curl->setOption(CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Content-Length: ' . strlen($postData)
            ]);
            
            $curl->post($url, []); 
            
            $status = $curl->getStatus();
            $body = $curl->getBody();

            if ($status >= 200 && $status < 300) {
                try {
                    $response = $this->json->unserialize($body);
                    if (isset($response['access_token'])) { 
                        return ['success' => true, 'token' => $response['access_token'], 'message' => 'Login Successful'];
                    } elseif (isset($response['token'])) {
                         return ['success' => true, 'token' => $response['token'], 'message' => 'Login Successful'];
                    }
                    return ['success' => false, 'message' => 'Token not found in response'];
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => 'Login JSON Parse Error: ' . $e->getMessage()];
                }
            }

            return ['success' => false, 'message' => "HTTP $status Error at $url"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Exception: ' . $e->getMessage()];
        }
    }
}
