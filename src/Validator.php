<?php

namespace LetoceilingCoder\VK;

use LetoceilingCoder\VK\Exceptions\VKValidationException;

/**
 * Валидатор для проверки данных перед отправкой в VK API
 */
class Validator
{
    /**
     * Валидировать текст сообщения
     */
    public static function validateMessageText(string $text): void
    {
        $length = mb_strlen($text);
        if ($length > Limits::MESSAGE_TEXT_MAX_LENGTH) {
            throw new VKValidationException(
                "Message text is too long ({$length} characters). Maximum: " . Limits::MESSAGE_TEXT_MAX_LENGTH
            );
        }
    }

    /**
     * Валидировать текст кнопки
     */
    public static function validateButtonText(string $text): void
    {
        if (empty($text)) {
            throw new VKValidationException('Button text cannot be empty');
        }

        $length = mb_strlen($text);
        if ($length > Limits::BUTTON_TEXT_MAX_LENGTH) {
            throw new VKValidationException(
                "Button text is too long ({$length} characters). Maximum: " . Limits::BUTTON_TEXT_MAX_LENGTH
            );
        }
    }

    /**
     * Валидировать payload кнопки
     */
    public static function validateButtonPayload(string $payload): void
    {
        $length = strlen($payload);
        if ($length > Limits::BUTTON_PAYLOAD_MAX_LENGTH) {
            throw new VKValidationException(
                "Button payload is too long ({$length} bytes). Maximum: " . Limits::BUTTON_PAYLOAD_MAX_LENGTH
            );
        }
    }

    /**
     * Валидировать URL
     */
    public static function validateUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new VKValidationException("Invalid URL: {$url}");
        }
    }

    /**
     * Валидировать клавиатуру
     */
    public static function validateKeyboard(array $buttons): void
    {
        $rowCount = count($buttons);
        if ($rowCount > Limits::KEYBOARD_ROWS_MAX) {
            throw new VKValidationException(
                "Too many rows in keyboard ({$rowCount}). Maximum: " . Limits::KEYBOARD_ROWS_MAX
            );
        }

        $totalButtons = 0;
        foreach ($buttons as $rowIndex => $row) {
            $buttonsInRow = count($row);
            $totalButtons += $buttonsInRow;

            if ($buttonsInRow > Limits::KEYBOARD_BUTTONS_PER_ROW_MAX) {
                throw new VKValidationException(
                    "Too many buttons in row {$rowIndex} ({$buttonsInRow}). Maximum: " . Limits::KEYBOARD_BUTTONS_PER_ROW_MAX
                );
            }
        }

        if ($totalButtons > Limits::KEYBOARD_BUTTONS_MAX) {
            throw new VKValidationException(
                "Too many buttons in keyboard ({$totalButtons}). Maximum: " . Limits::KEYBOARD_BUTTONS_MAX
            );
        }
    }

    /**
     * Валидировать цвет кнопки
     */
    public static function validateButtonColor(string $color): void
    {
        if (!in_array($color, Limits::BUTTON_COLORS)) {
            throw new VKValidationException(
                "Invalid button color: {$color}. Allowed: " . implode(', ', Limits::BUTTON_COLORS)
            );
        }
    }

    /**
     * Валидировать VK API версию
     */
    public static function validateApiVersion(string $version): void
    {
        if (!preg_match('/^\d+\.\d+$/', $version)) {
            throw new VKValidationException("Invalid API version format: {$version}");
        }
    }

    /**
     * Автоматически обрезать текст до допустимой длины
     */
    public static function truncateText(string $text, int $maxLength, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $maxLength) {
            return $text;
        }

        $suffixLength = mb_strlen($suffix);
        return mb_substr($text, 0, $maxLength - $suffixLength) . $suffix;
    }

    /**
     * Разбить длинный текст на несколько сообщений
     */
    public static function splitLongText(string $text, int $maxLength = null): array
    {
        $maxLength = $maxLength ?? Limits::MESSAGE_TEXT_MAX_LENGTH;
        
        if (mb_strlen($text) <= $maxLength) {
            return [$text];
        }

        $messages = [];
        $parts = explode("\n", $text);
        $currentMessage = '';

        foreach ($parts as $part) {
            $partLength = mb_strlen($part) + 1;
            
            if (mb_strlen($currentMessage) + $partLength <= $maxLength) {
                $currentMessage .= ($currentMessage ? "\n" : '') . $part;
            } else {
                if ($currentMessage) {
                    $messages[] = $currentMessage;
                }
                
                if (mb_strlen($part) > $maxLength) {
                    $chunks = mb_str_split($part, $maxLength);
                    foreach ($chunks as $chunk) {
                        $messages[] = $chunk;
                    }
                    $currentMessage = '';
                } else {
                    $currentMessage = $part;
                }
            }
        }

        if ($currentMessage) {
            $messages[] = $currentMessage;
        }

        return $messages;
    }
}

