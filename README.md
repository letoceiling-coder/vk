# VK Bot API для Laravel

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://www.php.net/)

Полноценная библиотека для работы с VK Bot API, Callback API и Mini App в Laravel приложениях.

## ✨ Возможности

- 🤖 **40+ методов Bot API** - отправка сообщений, работа с пользователями, группами
- 📱 **Mini App интеграция** - валидация параметров, работа с пользователями
- ⌨️ **Конструктор клавиатур** - 7 типов кнопок с цветами
- 🔄 **Callback API** - полная поддержка Callback API
- 📡 **Long Poll** - поддержка Long Poll API
- 👥 **Работа с сообществами** - управление группами
- ✅ **Автоматическая валидация** - проверка всех данных перед отправкой
- 📊 **Типы данных** - удобные классы для User, Chat, Message

## 📦 Установка

### Через Composer

```bash
composer require letoceiling-coder/vk
```

### Публикация конфигурации

```bash
php artisan vendor:publish --tag=vk-config
```

### Настройка .env

```env
VK_ACCESS_TOKEN=your_access_token_here
VK_SECRET_KEY=your_secret_key_here
VK_GROUP_ID=your_group_id
VK_API_VERSION=5.131

# Callback API
VK_CALLBACK_URL=https://your-domain.com/api/vk/callback
VK_CALLBACK_SECRET=your_callback_secret
VK_CONFIRMATION_CODE=your_confirmation_code

# VK Mini App
VK_APP_ID=your_app_id
VK_MINI_APP_URL=https://your-domain.com
VK_ADMIN_IDS=123456789,987654321
```

## 🚀 Быстрый старт

### Отправка сообщения

```php
use LetoceilingCoder\VK\VK;

// Простое сообщение
VK::send(123456789, 'Привет! 👋');

// С клавиатурой
$keyboard = VK::keyboard()
    ->row()
    ->text('Кнопка 1', 'btn1', 'primary')
    ->text('Кнопка 2', 'btn2', 'secondary')
    ->get();

VK::bot()->sendMessage(123456789, 'Выберите:', [
    'keyboard' => $keyboard
]);
```

### Создание клавиатуры

```php
use LetoceilingCoder\VK\VK;

// Полная клавиатура
$keyboard = VK::keyboard()
    ->row()
    ->text('🎰 Рулетка', 'wheel', 'primary')
    ->text('👥 Друзья', 'friends', 'secondary')
    ->row()
    ->link('📱 Сайт', 'https://example.com')
    ->row()
    ->openApp('🚀 Открыть приложение', config('vk.app_id'), -config('vk.group_id'))
    ->get();

// Inline клавиатура с callback
$keyboard = VK::keyboard()
    ->inline()
    ->row()
    ->callback('Кнопка 1', 'btn1')
    ->callback('Кнопка 2', 'btn2')
    ->get();

// Одноразовая клавиатура
$keyboard = VK::keyboard()
    ->oneTime()
    ->row()
    ->text('Отправить', 'send')
    ->get();

// Убрать клавиатуру
VK::bot()->sendMessage(123456789, 'Текст', [
    'keyboard' => VK::keyboard()->remove()
]);
```

### Валидация Mini App

```php
use LetoceilingCoder\VK\MiniApp;

$miniApp = new MiniApp();
$queryString = $request->getQueryString();

if ($miniApp->validateParams($queryString)) {
    $userId = $miniApp->getUserId($queryString);
    $platform = $miniApp->getPlatformInfo($queryString);
}
```

## 📚 Основные возможности

### Отправка сообщений

```php
use LetoceilingCoder\VK\VK;

// Текстовое сообщение
VK::bot()->sendMessage(123456789, 'Текст сообщения');

// С вложениями
VK::bot()->sendMessage(123456789, 'Фото:', [
    'attachment' => 'photo-123456_789012'
]);

// С клавиатурой
$keyboard = VK::keyboard()
    ->row()
    ->text('Да', 'yes', 'positive')
    ->text('Нет', 'no', 'negative')
    ->get();

VK::bot()->sendMessage(123456789, 'Выберите:', [
    'keyboard' => $keyboard
]);
```

### Цвета кнопок VK

```php
use LetoceilingCoder\VK\VK;

$keyboard = VK::keyboard()
    ->row()
    ->text('Синяя', 'btn1', 'primary')      // Синяя
    ->text('Белая', 'btn2', 'secondary')    // Белая (по умолчанию)
    ->text('Красная', 'btn3', 'negative')   // Красная
    ->text('Зелёная', 'btn4', 'positive')   // Зелёная
    ->get();
```

### Работа с пользователями

```php
use LetoceilingCoder\VK\VK;

// Получить информацию о пользователях
$users = VK::bot()->getUsers([123, 456, 789], ['photo_200', 'city', 'bdate']);

// Получить одного пользователя
$user = VK::bot()->getUser(123456789, ['photo_200']);

// Отправить действие (typing)
VK::bot()->sendActivity(123456789, 'typing');
```

### Callback API

```php
use LetoceilingCoder\VK\VK;

// Получить настройки Callback
$settings = VK::bot()->getCallbackSettings($groupId);

// Установить настройки
VK::bot()->setCallbackSettings($groupId, [
    'message_new' => 1,
    'message_reply' => 1,
    'message_edit' => 1,
]);

// Добавить callback сервер
VK::bot()->addCallbackServer($groupId, 'https://your-domain.com/api/vk/callback', 'Server Title', $secretKey);

// Получить код подтверждения
$code = VK::bot()->getCallbackConfirmationCode($groupId);
```

### Long Poll

```php
use LetoceilingCoder\VK\LongPoll;

$longPoll = new LongPoll();

// Получить сервер для Long Poll
$server = $longPoll->getServer($groupId);

// Получить обновления
$updates = $longPoll->getUpdates($server['server'], $server['key'], $server['ts']);
```

### Работа с сообществами

```php
use LetoceilingCoder\VK\Community;

$community = new Community();

// Получить информацию о сообществе
$group = $community->getGroup($groupId);

// Получить участников
$members = $community->getGroupMembers($groupId);

// Проверить участника
$isMember = $community->isGroupMember($groupId, $userId);
```

## 🎯 Использование в контроллерах

### Callback API обработчик

```php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use LetoceilingCoder\VK\VK;

class VKCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // Подтверждение сервера
        if ($request->input('type') === 'confirmation') {
            return response(config('vk.callback.confirmation_code'), 200);
        }
        
        $update = $request->all();
        
        if ($update['type'] === 'message_new') {
            $this->handleMessage($update['object']['message']);
        }
        
        return response()->json(['ok' => true], 200);
    }
    
    protected function handleMessage($message)
    {
        $peerId = $message['peer_id'];
        $text = $message['text'] ?? '';
        
        if ($text === '/start') {
            VK::send($peerId, 'Добро пожаловать!');
        }
    }
}
```

### Middleware для Mini App

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LetoceilingCoder\VK\MiniApp;
use LetoceilingCoder\VK\Exceptions\VKValidationException;

class VKAuth
{
    public function handle(Request $request, Closure $next)
    {
        $queryString = $request->getQueryString();
        
        if (!$queryString) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $miniApp = new MiniApp();
            $userId = $miniApp->validateAndGetUserId($queryString);
            $request->merge(['vk_user_id' => $userId]);
        } catch (VKValidationException $e) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        return $next($request);
    }
}
```

## 📖 API Документация

### Основные классы

- **`VK`** - Фасад для быстрого доступа
- **`Bot`** - Работа с Bot API (40+ методов)
- **`MiniApp`** - Валидация Mini App
- **`Community`** - Работа с сообществами
- **`LongPoll`** - Long Poll API
- **`Keyboard`** - Конструктор клавиатур

### Основные методы Bot API

#### Messages (9 методов)
- `sendMessage()` - Отправить сообщение
- `editMessage()` - Редактировать сообщение
- `deleteMessage()` - Удалить сообщение
- `getMessagesById()` - Получить сообщения по ID
- `getHistory()` - Получить историю сообщений
- `getConversations()` - Получить диалоги
- `markAsRead()` - Отметить как прочитанное
- `sendActivity()` - Отправить действие (typing)

#### Users (2 метода)
- `getUsers()` - Получить информацию о пользователях
- `getUser()` - Получить информацию об одном пользователе

#### Groups (3 метода)
- `getGroup()` - Получить информацию о сообществе
- `getGroupMembers()` - Получить участников
- `isGroupMember()` - Проверить участника

#### Callback API (6 методов)
- `getCallbackSettings()` - Получить настройки
- `setCallbackSettings()` - Установить настройки
- `getCallbackServers()` - Получить серверы
- `addCallbackServer()` - Добавить сервер
- `deleteCallbackServer()` - Удалить сервер
- `getCallbackConfirmationCode()` - Получить код подтверждения

Полный список методов смотрите в [src/README.md](src/README.md)

## 🚨 Обработка ошибок

```php
use LetoceilingCoder\VK\Exceptions\VKException;

try {
    VK::send(123456789, 'Hello');
} catch (VKException $e) {
    Log::error('VK API error: ' . $e->getMessage());
}
```

## 🛠️ Дополнительная документация

- **[src/README.md](src/README.md)** - Полная документация с примерами
- **[src/EXAMPLES.md](src/EXAMPLES.md)** - Примеры использования
- **[src/LIMITS.md](src/LIMITS.md)** - Лимиты и валидация
- **[src/FEATURES.md](src/FEATURES.md)** - Полный список возможностей
- **[src/SETUP.md](src/SETUP.md)** - Подробная инструкция по установке

## 🔗 Официальная документация VK

- [VK Bot API](https://dev.vk.com/api/bots)
- [VK Mini Apps](https://dev.vk.com/mini-apps)
- [Callback API](https://dev.vk.com/api/callback/getting-started)
- [VK API Methods](https://dev.vk.com/method)

## 📝 Лицензия

MIT License. См. [LICENSE](LICENSE) файл для деталей.

## 🤝 Поддержка

Если у вас есть вопросы или предложения, создайте [Issue](https://github.com/letoceiling-coder/vk/issues) в репозитории.
