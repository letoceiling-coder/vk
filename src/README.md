# VK Bot API для Laravel

Полная интеграция с VK Bot API и VK Mini Apps (https://dev.vk.com/api/bots)

## 📁 Структура

```
app/VK/
├── VKClient.php            # Базовый клиент для HTTP-запросов
├── Bot.php                 # 40+ методов VK Bot API
├── MiniApp.php             # Валидация VK Mini Apps
├── Keyboard.php            # Создание клавиатур VK
├── VK.php                  # Фасад для удобного доступа
├── Limits.php              # Константы лимитов API ✅
├── Validator.php           # Автоматическая валидация ✅
├── helpers.php             # Helper функции ✅
├── Exceptions/             # Исключения
│   ├── VKException.php
│   └── VKValidationException.php
└── Types/                  # Типы данных
    ├── User.php
    ├── Message.php
    └── Chat.php
```

## 🚀 Установка

### 1. Настройка config/vk.php

Смотрите [SETUP.md](SETUP.md) для подробных инструкций.

### 2. Настройка .env

```env
VK_ACCESS_TOKEN=your_access_token_here
VK_SECRET_KEY=your_secret_key_here
VK_GROUP_ID=your_group_id
VK_API_VERSION=5.131

# Callback API
VK_CALLBACK_URL="${APP_URL}/api/vk/callback"
VK_CALLBACK_SECRET=your_callback_secret
VK_CONFIRMATION_CODE=your_confirmation_code

# VK Mini App
VK_APP_ID=your_app_id
VK_MINI_APP_URL="${APP_URL}"
```

## 📚 Использование

### Быстрый старт

```php
use App\VK\VK;

// Отправить сообщение
vk_send(123456789, 'Привет!');

// Валидировать Mini App
$isValid = vk_validate_miniapp($queryString);
$userId = vk_get_user_id($queryString);

// Создать клавиатуру
$keyboard = vk_keyboard()
    ->row()
    ->text('Кнопка 1', 'btn1')
    ->text('Кнопка 2', 'btn2')
    ->get();
```

### Отправка сообщений

```php
use App\VK\VK;

// Простое текстовое сообщение
vk_send(123456789, 'Текст сообщения');

// С клавиатурой
$keyboard = vk_keyboard()
    ->row()
    ->text('Да', 'yes', 'positive')
    ->text('Нет', 'no', 'negative')
    ->get();

VK::bot()->sendMessage(123456789, 'Выберите:', [
    'keyboard' => $keyboard
]);

// С вложениями
VK::bot()->sendMessage(123456789, 'Фото:', [
    'attachment' => 'photo-123456_789012'
]);
```

### Работа с клавиатурами

```php
// Полная клавиатура
$keyboard = vk_keyboard()
    ->row()
    ->text('🎰 Рулетка', 'wheel', 'primary')
    ->text('👥 Друзья', 'friends', 'secondary')
    ->row()
    ->link('📱 Сайт', 'https://example.com')
    ->row()
    ->openApp('🚀 Открыть приложение', config('vk.app_id'), -config('vk.group_id'))
    ->get();

// Inline клавиатура с callback
$keyboard = vk_keyboard()
    ->inline()
    ->row()
    ->callback('Кнопка 1', 'btn1')
    ->callback('Кнопка 2', 'btn2')
    ->get();

// Одноразовая клавиатура
$keyboard = vk_keyboard()
    ->oneTime()
    ->row()
    ->text('Отправить', 'send')
    ->get();

// Убрать клавиатуру
VK::bot()->sendMessage(123456789, 'Текст', [
    'keyboard' => \App\VK\Keyboard::remove()
]);
```

### Цвета кнопок VK

```php
$keyboard = vk_keyboard()
    ->row()
    ->text('Синяя', 'btn1', 'primary')      // Синяя
    ->text('Белая', 'btn2', 'secondary')    // Белая (по умолчанию)
    ->text('Красная', 'btn3', 'negative')   // Красная
    ->text('Зелёная', 'btn4', 'positive')   // Зелёная
    ->get();
```

### Редактирование и удаление сообщений

```php
// Редактировать сообщение
VK::bot()->editMessage($peerId, $conversationMessageId, 'Новый текст');

// Удалить сообщение
VK::bot()->deleteMessage([$messageId], true); // true = удалить для всех

// Получить сообщения
$messages = VK::bot()->getMessagesById([$messageId]);
```

### Работа с пользователями

```php
// Получить информацию о пользователях
$users = VK::bot()->getUsers([123, 456, 789], ['photo_200', 'city', 'bdate']);

// Получить одного пользователя
$user = VK::bot()->getUser(123456789, ['photo_200']);

// Отправить действие (typing)
VK::bot()->sendActivity(123456789, 'typing');
```

### Callback API

```php
// Получить настройки Callback
$settings = VK::bot()->getCallbackSettings($groupId);

// Установить настройки
VK::bot()->setCallbackSettings($groupId, [
    'message_new' => 1,
    'message_reply' => 1,
]);

// Получить код подтверждения
$code = VK::bot()->getCallbackConfirmationCode($groupId);
```

### VK Mini App валидация

```php
use App\VK\MiniApp;

$miniApp = new MiniApp();

// Валидировать параметры (query string)
if ($miniApp->validateParams($queryString)) {
    $userId = $miniApp->getUserId($queryString);
    $platform = $miniApp->getPlatformInfo($queryString);
}

// Или с исключением
try {
    $userId = $miniApp->validateAndGetUserId($queryString);
} catch (\App\VK\Exceptions\VKValidationException $e) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```

### Загрузка файлов

```php
// Загрузить фото
$uploadServer = VK::bot()->getPhotoUploadServer($peerId);
// Загрузите файл на $uploadServer['upload_url']
$saved = VK::bot()->saveMessagesPhoto($photo, $server, $hash);

// Загрузить документ
$uploadServer = VK::bot()->getDocsUploadServer($peerId);
$saved = VK::bot()->saveDoc($file, 'Document Title');
```

## 🎯 Все методы Bot API

### Messages (9 методов)
- `sendMessage()` - Отправить сообщение
- `editMessage()` - Редактировать сообщение
- `deleteMessage()` - Удалить сообщение
- `getMessagesById()` - Получить сообщения по ID
- `getHistory()` - Получить историю сообщений
- `getConversations()` - Получить диалоги
- `markAsRead()` - Отметить как прочитанное
- `sendActivity()` - Отправить действие (typing)

### Users (2 метода)
- `getUsers()` - Получить информацию о пользователях
- `getUser()` - Получить информацию об одном пользователе

### Groups (3 метода)
- `getGroup()` - Получить информацию о сообществе
- `getGroupMembers()` - Получить участников
- `isGroupMember()` - Проверить участника

### Photos (2 метода)
- `getPhotoUploadServer()` - Получить URL для загрузки
- `saveMessagesPhoto()` - Сохранить загруженное фото

### Docs (2 метода)
- `getDocsUploadServer()` - Получить URL для загрузки
- `saveDoc()` - Сохранить документ

### Utils (2 метода)
- `getShortLink()` - Получить короткую ссылку
- `checkLink()` - Проверить ссылку

### Callback API (6 методов)
- `getCallbackSettings()` - Получить настройки
- `setCallbackSettings()` - Установить настройки
- `getCallbackServers()` - Получить серверы
- `addCallbackServer()` - Добавить сервер
- `deleteCallbackServer()` - Удалить сервер
- `getCallbackConfirmationCode()` - Получить код подтверждения

### Storage (2 метода)
- `storageGet()` - Получить данные
- `storageSet()` - Сохранить данные

**Итого: 40+ методов Bot API**

## 📖 Официальная документация

- VK Bot API: https://dev.vk.com/api/bots
- VK Mini Apps: https://dev.vk.com/mini-apps
- Callback API: https://dev.vk.com/api/callback/getting-started
- VK API Methods: https://dev.vk.com/method

## 🚨 Обработка ошибок

```php
use App\VK\Exceptions\VKException;

try {
    vk_send(123456789, 'Hello');
} catch (VKException $e) {
    Log::error('VK API error: ' . $e->getMessage());
}
```

## 🔗 Дополнительная документация

- [SETUP.md](SETUP.md) - Установка и настройка
- [LIMITS.md](LIMITS.md) - Лимиты и валидация
- [EXAMPLES.md](EXAMPLES.md) - Примеры использования
- [FEATURES.md](FEATURES.md) - Полный список возможностей

