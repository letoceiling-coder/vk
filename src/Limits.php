<?php

namespace LetoceilingCoder\VK;

/**
 * Константы лимитов и ограничений VK API
 * Документация: https://dev.vk.com/api/bots/development/limits
 */
class Limits
{
    // ==========================================
    // Клавиатура
    // ==========================================
    
    /** Максимальное количество кнопок в клавиатуре */
    public const KEYBOARD_BUTTONS_MAX = 40;
    
    /** Максимальное количество рядов в клавиатуре */
    public const KEYBOARD_ROWS_MAX = 10;
    
    /** Максимальное количество кнопок в одном ряду */
    public const KEYBOARD_BUTTONS_PER_ROW_MAX = 5;
    
    /** Максимальная длина текста на кнопке */
    public const BUTTON_TEXT_MAX_LENGTH = 40;
    
    /** Максимальная длина payload кнопки */
    public const BUTTON_PAYLOAD_MAX_LENGTH = 255;
    
    // ==========================================
    // Сообщения
    // ==========================================
    
    /** Максимальная длина текста сообщения */
    public const MESSAGE_TEXT_MAX_LENGTH = 4096;
    
    /** Максимальное количество вложений в сообщении */
    public const MESSAGE_ATTACHMENTS_MAX = 10;
    
    /** Максимальное количество пересылаемых сообщений */
    public const MESSAGE_FORWARD_MAX = 10;
    
    // ==========================================
    // Файлы
    // ==========================================
    
    /** Максимальный размер фотографии (50 MB) */
    public const PHOTO_MAX_SIZE = 50 * 1024 * 1024;
    
    /** Максимальный размер документа (200 MB) */
    public const DOC_MAX_SIZE = 200 * 1024 * 1024;
    
    /** Максимальный размер аудио (200 MB) */
    public const AUDIO_MAX_SIZE = 200 * 1024 * 1024;
    
    /** Максимальный размер видео (файл, не через сервер ВК) */
    public const VIDEO_MAX_SIZE = 2 * 1024 * 1024 * 1024; // 2 GB
    
    // ==========================================
    // Rate Limits
    // ==========================================
    
    /** Максимум запросов в секунду */
    public const API_REQUESTS_PER_SECOND = 20;
    
    /** Максимум сообщений в секунду */
    public const MESSAGES_PER_SECOND = 20;
    
    /** Максимум сообщений в день для одного пользователя */
    public const MESSAGES_PER_DAY_PER_USER = 500;
    
    // ==========================================
    // API Version
    // ==========================================
    
    /** Рекомендуемая версия API */
    public const API_VERSION = '5.131';
    
    // ==========================================
    // Callback API
    // ==========================================
    
    /** Максимальное количество Callback серверов */
    public const CALLBACK_SERVERS_MAX = 10;
    
    /** Timeout для Callback запросов (секунды) */
    public const CALLBACK_TIMEOUT = 10;
    
    // ==========================================
    // Storage
    // ==========================================
    
    /** Максимальная длина ключа в Storage */
    public const STORAGE_KEY_MAX_LENGTH = 100;
    
    /** Максимальная длина значения в Storage */
    public const STORAGE_VALUE_MAX_LENGTH = 4096;
    
    /** Максимальное количество ключей в Storage */
    public const STORAGE_KEYS_MAX = 1000;
    
    // ==========================================
    // Цвета кнопок VK
    // ==========================================
    
    /** Доступные цвета кнопок */
    public const BUTTON_COLORS = [
        'primary',    // Синяя
        'secondary',  // Белая
        'negative',   // Красная
        'positive',   // Зелёная
    ];
}

