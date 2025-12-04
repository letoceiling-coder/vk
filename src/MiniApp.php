<?php

namespace LetoceilingCoder\VK;

use LetoceilingCoder\VK\Exceptions\VKValidationException;
use Illuminate\Support\Facades\Log;

/**
 * Класс для работы с VK Mini Apps (VK Apps)
 * Документация: https://dev.vk.com/mini-apps/development/launch-params
 */
class MiniApp
{
    protected string $secretKey;

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? config('vk.secret_key');
        
        if (!$this->secretKey) {
            throw new VKValidationException('VK secret key not configured');
        }
    }

    /**
     * Валидировать параметры запуска VK Mini App
     * 
     * @param string $queryString - Query string из URL (все после ?)
     * @return bool
     */
    public function validateParams(string $queryString): bool
    {
        try {
            parse_str($queryString, $params);
            
            if (!isset($params['sign'])) {
                Log::warning('VK Mini App: sign not found');
                return false;
            }

            $sign = $params['sign'];
            unset($params['sign']);

            // Сортируем параметры по ключу
            ksort($params);
            
            // Формируем строку для проверки
            $paramsString = http_build_query($params);
            
            // Вычисляем sign
            $calculatedSign = base64_encode(hash_hmac('sha256', $paramsString, $this->secretKey, true));
            
            // Заменяем символы для URL-safe base64
            $calculatedSign = rtrim(strtr($calculatedSign, '+/', '-_'), '=');

            $isValid = hash_equals($calculatedSign, $sign);

            if (!$isValid) {
                Log::warning('VK Mini App: invalid sign', [
                    'expected' => $calculatedSign,
                    'received' => $sign,
                ]);
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('VK Mini App validation error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Парсить параметры запуска
     * 
     * @param string $queryString
     * @return array
     */
    public function parseParams(string $queryString): array
    {
        parse_str($queryString, $params);
        
        // Декодируем JSON поля
        if (isset($params['vk_user_id'])) {
            $params['vk_user_id'] = (int) $params['vk_user_id'];
        }
        
        if (isset($params['vk_app_id'])) {
            $params['vk_app_id'] = (int) $params['vk_app_id'];
        }
        
        if (isset($params['vk_group_id'])) {
            $params['vk_group_id'] = (int) $params['vk_group_id'];
        }
        
        return $params;
    }

    /**
     * Получить ID пользователя из параметров
     * 
     * @param string $queryString
     * @return int|null
     */
    public function getUserId(string $queryString): ?int
    {
        $params = $this->parseParams($queryString);
        return $params['vk_user_id'] ?? null;
    }

    /**
     * Получить данные о платформе
     * 
     * @param string $queryString
     * @return array
     */
    public function getPlatformInfo(string $queryString): array
    {
        $params = $this->parseParams($queryString);
        
        return [
            'platform' => $params['vk_platform'] ?? null,
            'is_favorite' => isset($params['vk_is_favorite']) && $params['vk_is_favorite'] === '1',
            'language' => $params['vk_language'] ?? 'ru',
            'ref' => $params['vk_ref'] ?? null,
        ];
    }

    /**
     * Валидировать и получить ID пользователя
     * Бросает исключение если данные невалидны
     * 
     * @param string $queryString
     * @return int
     * @throws VKValidationException
     */
    public function validateAndGetUserId(string $queryString): int
    {
        if (!$this->validateParams($queryString)) {
            throw new VKValidationException('Invalid VK Mini App params signature');
        }

        $userId = $this->getUserId($queryString);
        
        if (!$userId) {
            throw new VKValidationException('User ID not found in params');
        }

        return $userId;
    }

    /**
     * Создать URL для открытия VK Mini App
     * 
     * @param int $appId - ID приложения
     * @param string $hash - Хэш для навигации
     * @return string
     */
    public function createAppUrl(int $appId, string $hash = ''): string
    {
        $url = "https://vk.com/app{$appId}";
        
        if ($hash) {
            $url .= '#' . $hash;
        }
        
        return $url;
    }
}

