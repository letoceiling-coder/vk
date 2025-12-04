<?php

namespace LetoceilingCoder\VK;

/**
 * Фасад для удобного доступа к VK API
 */
class VK
{
    /**
     * Получить экземпляр Bot
     */
    public static function bot(?string $accessToken = null): Bot
    {
        return new Bot($accessToken);
    }

    /**
     * Получить экземпляр MiniApp
     */
    public static function miniApp(?string $secretKey = null): MiniApp
    {
        return new MiniApp($secretKey);
    }

    /**
     * Получить экземпляр Community
     */
    public static function community(?string $accessToken = null, ?int $groupId = null): Community
    {
        return new Community($accessToken, $groupId);
    }

    /**
     * Получить экземпляр LongPoll
     */
    public static function longPoll(?string $accessToken = null, ?int $groupId = null): LongPoll
    {
        return new LongPoll($accessToken, $groupId);
    }

    /**
     * Создать клавиатуру
     */
    public static function keyboard(): Keyboard
    {
        return new Keyboard();
    }

    /**
     * Быстрая отправка сообщения
     */
    public static function send(int|string $userId, string $message, array $params = []): array
    {
        return static::bot()->sendMessage($userId, $message, $params);
    }

    /**
     * Валидировать VK Mini App параметры
     */
    public static function validateMiniApp(string $queryString): bool
    {
        return static::miniApp()->validateParams($queryString);
    }

    /**
     * Получить ID пользователя из VK Mini App параметров
     */
    public static function getMiniAppUserId(string $queryString): ?int
    {
        return static::miniApp()->getUserId($queryString);
    }
}

