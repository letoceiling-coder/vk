<?php

namespace LetoceilingCoder\VK;

/**
 * Класс для создания клавиатур VK
 * Документация: https://dev.vk.com/api/bots/development/keyboard
 * 
 * Лимиты:
 * - Максимум 40 кнопок в клавиатуре
 * - Максимум 10 рядов
 * - Максимум 5 кнопок в ряду (для text, link, vkpay, open_app)
 * - Максимум 4 кнопки в ряду для location и vkpay
 */
class Keyboard
{
    protected array $buttons = [];
    protected bool $oneTime = false;
    protected bool $inline = false;

    /**
     * Добавить новый ряд кнопок
     */
    public function row(): self
    {
        $this->buttons[] = [];
        return $this;
    }

    /**
     * Сделать клавиатуру одноразовой (скрывается после нажатия)
     */
    public function oneTime(bool $oneTime = true): self
    {
        $this->oneTime = $oneTime;
        return $this;
    }

    /**
     * Сделать клавиатуру inline (прикрепляется к сообщению)
     */
    public function inline(bool $inline = true): self
    {
        $this->inline = $inline;
        return $this;
    }

    /**
     * Добавить текстовую кнопку
     * 
     * @param string $label - Текст на кнопке
     * @param string $payload - Данные (JSON)
     * @param string $color - Цвет кнопки (primary, secondary, negative, positive)
     */
    public function text(string $label, string $payload = '', string $color = 'secondary'): self
    {
        Validator::validateButtonText($label);
        Validator::validateButtonColor($color);
        
        $payloadToUse = $payload ?: json_encode(['button' => $label]);
        Validator::validateButtonPayload($payloadToUse);
        
        return $this->addButton([
            'action' => [
                'type' => 'text',
                'label' => $label,
                'payload' => $payloadToUse,
            ],
            'color' => $color,
        ]);
    }

    /**
     * Добавить callback кнопку (для inline клавиатур)
     * 
     * @param string $label - Текст на кнопке
     * @param string $payload - Данные callback
     */
    public function callback(string $label, string $payload): self
    {
        Validator::validateButtonText($label);
        
        $jsonPayload = json_encode(['callback' => $payload]);
        Validator::validateButtonPayload($jsonPayload);
        
        return $this->addButton([
            'action' => [
                'type' => 'callback',
                'label' => $label,
                'payload' => $jsonPayload,
            ],
        ]);
    }

    /**
     * Добавить кнопку со ссылкой
     * 
     * @param string $label - Текст на кнопке
     * @param string $link - URL ссылки
     */
    public function link(string $label, string $link): self
    {
        Validator::validateButtonText($label);
        Validator::validateUrl($link);
        
        return $this->addButton([
            'action' => [
                'type' => 'open_link',
                'label' => $label,
                'link' => $link,
            ],
        ]);
    }

    /**
     * Добавить кнопку запроса геолокации
     */
    public function location(string $label = 'Отправить местоположение'): self
    {
        return $this->addButton([
            'action' => [
                'type' => 'location',
                'payload' => json_encode(['location' => true]),
            ],
        ]);
    }

    /**
     * Добавить кнопку VK Pay
     * 
     * @param string $hash - VK Pay хэш
     */
    public function vkPay(string $hash): self
    {
        return $this->addButton([
            'action' => [
                'type' => 'vkpay',
                'hash' => $hash,
            ],
        ]);
    }

    /**
     * Добавить кнопку открытия VK Mini App
     * 
     * @param string $label - Текст на кнопке
     * @param int $appId - ID приложения
     * @param int $ownerId - ID владельца приложения
     * @param string $hash - Хэш для навигации в приложении
     */
    public function openApp(string $label, int $appId, int $ownerId, string $hash = ''): self
    {
        return $this->addButton([
            'action' => [
                'type' => 'open_app',
                'label' => $label,
                'app_id' => $appId,
                'owner_id' => $ownerId,
                'hash' => $hash,
            ],
        ]);
    }

    /**
     * Добавить кнопку в текущий ряд
     */
    protected function addButton(array $button): self
    {
        if (empty($this->buttons)) {
            $this->row();
        }

        $lastRowIndex = count($this->buttons) - 1;
        $this->buttons[$lastRowIndex][] = $button;

        return $this;
    }

    /**
     * Получить JSON для отправки с сообщением
     */
    public function get(): string
    {
        // Валидация клавиатуры
        Validator::validateKeyboard($this->buttons);

        $keyboard = [
            'one_time' => $this->oneTime,
            'buttons' => $this->buttons,
            'inline' => $this->inline,
        ];

        return json_encode($keyboard, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Получить массив клавиатуры
     */
    public function toArray(): array
    {
        return [
            'one_time' => $this->oneTime,
            'buttons' => $this->buttons,
            'inline' => $this->inline,
        ];
    }

    /**
     * Создать пустую клавиатуру (скрыть клавиатуру)
     */
    public static function remove(): string
    {
        return json_encode(['buttons' => []]);
    }

    /**
     * Быстрое создание текстовой клавиатуры
     * 
     * @param array $buttons - Массив ['Текст' => 'payload', ...]
     * @param int $columns - Количество кнопок в ряду
     * @param string $color - Цвет кнопок
     * @return string
     */
    public static function makeText(array $buttons, int $columns = 3, string $color = 'secondary'): string
    {
        $keyboard = new self();
        $count = 0;

        foreach ($buttons as $label => $payload) {
            if ($count % $columns === 0) {
                $keyboard->row();
            }
            $keyboard->text($label, is_string($payload) ? $payload : json_encode($payload), $color);
            $count++;
        }

        return $keyboard->get();
    }

    /**
     * Быстрое создание inline клавиатуры с callback кнопками
     * 
     * @param array $buttons - Массив ['Текст' => 'callback', ...]
     * @param int $columns - Количество кнопок в ряду
     * @return string
     */
    public static function makeCallbacks(array $buttons, int $columns = 3): string
    {
        $keyboard = (new self())->inline();
        $count = 0;

        foreach ($buttons as $label => $payload) {
            if ($count % $columns === 0) {
                $keyboard->row();
            }
            $keyboard->callback($label, $payload);
            $count++;
        }

        return $keyboard->get();
    }
}

