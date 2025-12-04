<?php

namespace LetoceilingCoder\VK;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use LetoceilingCoder\VK\Exceptions\VKException;

/**
 * Базовый класс для работы с VK API
 * Документация: https://dev.vk.com/api/bots
 */
class VKClient
{
    protected string $accessToken;
    protected string $apiVersion;
    protected string $baseUrl = 'https://api.vk.com/method/';

    public function __construct(?string $accessToken = null, ?string $apiVersion = null)
    {
        $this->accessToken = $accessToken ?? config('vk.access_token');
        $this->apiVersion = $apiVersion ?? config('vk.api_version', '5.131');
        
        if (!$this->accessToken) {
            throw new VKException('VK access token not configured');
        }
    }

    /**
     * Выполнить запрос к VK API
     * 
     * @param string $method - Название метода API (например, 'messages.send')
     * @param array $params - Параметры запроса
     * @return array
     */
    protected function call(string $method, array $params = []): array
    {
        $url = $this->baseUrl . $method;

        // Добавляем обязательные параметры
        $params['access_token'] = $this->accessToken;
        $params['v'] = $this->apiVersion;

        try {
            $response = Http::timeout(30)
                ->asForm()
                ->post($url, $params);

            $data = $response->json();

            // Проверка на ошибки VK API
            if (isset($data['error'])) {
                $error = $data['error'];
                $errorCode = $error['error_code'] ?? 0;
                $errorMsg = $error['error_msg'] ?? 'Unknown error';
                
                Log::error('VK API error', [
                    'method' => $method,
                    'error_code' => $errorCode,
                    'error_msg' => $errorMsg,
                    'params' => $params,
                ]);

                throw new VKException("[{$errorCode}] {$errorMsg}");
            }

            return $data['response'] ?? [];

        } catch (VKException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('VK API request failed', [
                'method' => $method,
                'error' => $e->getMessage(),
            ]);
            throw new VKException('Failed to execute VK API request: ' . $e->getMessage());
        }
    }

    /**
     * Выполнить batch запрос (execute)
     * 
     * @param array $methods - Массив методов для выполнения
     * @return array
     */
    protected function execute(array $methods): array
    {
        $code = 'return [' . implode(',', $methods) . '];';
        return $this->call('execute', ['code' => $code]);
    }

    /**
     * Загрузить документ/фото
     * 
     * @param string $uploadUrl - URL для загрузки
     * @param string $fieldName - Название поля (file, photo и т.д.)
     * @param string $filePath - Путь к файлу
     * @return array
     */
    protected function upload(string $uploadUrl, string $fieldName, string $filePath): array
    {
        try {
            $response = Http::timeout(60)
                ->attach($fieldName, fopen($filePath, 'r'), basename($filePath))
                ->post($uploadUrl);

            if ($response->failed()) {
                throw new VKException('File upload failed');
            }

            return $response->json() ?? [];

        } catch (\Exception $e) {
            throw new VKException('Failed to upload file: ' . $e->getMessage());
        }
    }

    /**
     * Получить random_id для сообщения
     */
    protected function getRandomId(): int
    {
        return random_int(0, PHP_INT_MAX);
    }

    /**
     * Логирование для отладки
     */
    protected function log(string $message, array $context = []): void
    {
        Log::info('[VK] ' . $message, $context);
    }
}

