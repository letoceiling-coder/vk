<?php

use LetoceilingCoder\VK\Bot;
use LetoceilingCoder\VK\MiniApp;
use LetoceilingCoder\VK\Keyboard;

if (!function_exists('vk')) {
    /**
     * Получить экземпляр VK Bot
     */
    function vk(): Bot
    {
        return app('vk.bot');
    }
}

if (!function_exists('vk_miniapp')) {
    /**
     * Получить экземпляр VK MiniApp
     */
    function vk_miniapp(): MiniApp
    {
        return app('vk.miniapp');
    }
}

if (!function_exists('vk_send')) {
    /**
     * Быстрая отправка сообщения
     * 
     * @param int|string $userId
     * @param string $message
     * @param array $params
     * @return array
     */
    function vk_send(int|string $userId, string $message, array $params = []): array
    {
        return vk()->sendMessage($userId, $message, $params);
    }
}

if (!function_exists('vk_keyboard')) {
    /**
     * Создать клавиатуру VK
     */
    function vk_keyboard(): Keyboard
    {
        return new Keyboard();
    }
}

if (!function_exists('vk_validate_miniapp')) {
    /**
     * Валидировать VK Mini App параметры
     * 
     * @param string $queryString
     * @return bool
     */
    function vk_validate_miniapp(string $queryString): bool
    {
        return vk_miniapp()->validateParams($queryString);
    }
}

if (!function_exists('vk_get_user_id')) {
    /**
     * Получить VK user ID из параметров Mini App
     * 
     * @param string $queryString
     * @return int|null
     */
    function vk_get_user_id(string $queryString): ?int
    {
        return vk_miniapp()->getUserId($queryString);
    }
}

if (!function_exists('vk_is_admin')) {
    /**
     * Проверить является ли пользователь администратором
     * 
     * @param int $userId
     * @return bool
     */
    function vk_is_admin(int $userId): bool
    {
        $adminIds = config('vk.admin_ids', []);
        return in_array($userId, $adminIds);
    }
}

if (!function_exists('vk_attachment')) {
    /**
     * Создать строку вложения для VK
     * 
     * @param string $type - Тип (photo, video, audio, doc, wall, market)
     * @param int $ownerId - ID владельца
     * @param int $mediaId - ID медиа
     * @param string $accessKey - Ключ доступа (опционально)
     * @return string
     */
    function vk_attachment(string $type, int $ownerId, int $mediaId, string $accessKey = ''): string
    {
        $attachment = "{$type}{$ownerId}_{$mediaId}";
        
        if ($accessKey) {
            $attachment .= "_{$accessKey}";
        }
        
        return $attachment;
    }
}

