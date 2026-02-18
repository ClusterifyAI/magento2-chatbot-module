<?php
namespace ClusterifyAI\Chatbot\Api;

/**
 * Interface AuthTokenProviderInterface
 */
interface AuthTokenProviderInterface
{
    /**
     * Get the bearer token, using cached version if available and valid.
     *
     * @param string|null $publicKey
     * @param string|null $secretKey
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToken($publicKey = null, $secretKey = null);

    /**
     * Test the API connection and return result.
     *
     * @param string $baseUrl
     * @param string $loginPath
     * @param string $publicKey
     * @param string $secretKey
     * @return array
     */
    public function testConnection($baseUrl, $loginPath, $publicKey, $secretKey);

    /**
     * Test full connection: Widget Load, Authentication, and ChatBot Status
     *
     * @param string $baseUrl
     * @param string $loginPath
     * @param string $publicKey
     * @param string $secretKey
     * @param string|null $loaderPath
     * @param string|null $statusCheckPath
     * @param string|null $uuid
     * @return array
     */
    public function testFullConnection($baseUrl, $loginPath, $publicKey, $secretKey, $loaderPath = null, $statusCheckPath = null, $uuid = null);
}
