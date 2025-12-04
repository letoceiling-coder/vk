# VK Bot API для Laravel

Полноценная библиотека для работы с VK Bot API, Callback API и Mini App.

## Установка

```bash
composer require letoceiling-coder/vk
```

## Настройка

### 1. Публикация конфигурации

```bash
php artisan vendor:publish --tag=vk-config
```

### 2. Настройка .env

```env
VK_ACCESS_TOKEN=your_access_token_here
VK_SECRET_KEY=your_secret_key_here
VK_GROUP_ID=your_group_id
VK_API_VERSION=5.131
VK_CALLBACK_URL=https://your-domain.com/api/vk/callback
VK_MINI_APP_URL=https://your-domain.com
```

## Использование

### Через фасад

```php
use LetoceilingCoder\VK\VK;

// Отправить сообщение
VK::send(123456789, 'Привет!');

// Создать клавиатуру
$keyboard = VK::keyboard()
    ->callback('Кнопка', 'callback_data')
    ->toArray();

VK::bot()->sendMessage(123456789, 'Выберите действие', [
    'keyboard' => $keyboard
]);
```

### Через сервис контейнер

```php
use LetoceilingCoder\VK\Bot;

$bot = app(Bot::class);
$bot->sendMessage(123456789, 'Привет!');
```

## Документация

Подробная документация находится в `src/README.md` после установки пакета.

## Лицензия

MIT

