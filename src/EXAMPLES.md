# Примеры использования VK API

## Простые примеры

### Отправка сообщений

```php
use App\VK\VK;

// Простое сообщение
vk_send(123456789, 'Привет!');

// С клавиатурой
$keyboard = vk_keyboard()
    ->row()
    ->text('Да', 'yes', 'positive')
    ->text('Нет', 'no', 'negative')
    ->get();

VK::bot()->sendMessage(123456789, 'Согласны?', [
    'keyboard' => $keyboard
]);

// С вложением
VK::bot()->sendMessage(123456789, 'Фото:', [
    'attachment' => vk_attachment('photo', -123456, 789012)
]);
```

### Клавиатуры

```php
// Полная клавиатура с разными кнопками
$keyboard = vk_keyboard()
    ->row()
    ->text('🎰 Рулетка', 'wheel', 'primary')
    ->text('👥 Друзья', 'friends')
    ->text('🏆 Топ', 'top')
    ->row()
    ->link('📱 Сайт', 'https://example.com')
    ->row()
    ->openApp('🚀 Открыть приложение', config('vk.app_id'), -config('vk.group_id'))
    ->row()
    ->location('📍 Моё местоположение')
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

## Примеры в контроллерах

### 1. Callback API обработчик

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\VK\VK;
use Illuminate\Http\Request;

class VKCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $type = $request->input('type');
        $object = $request->input('object');
        
        // Подтверждение сервера
        if ($type === 'confirmation') {
            return response(config('vk.callback.confirmation_code'));
        }
        
        if ($type === 'message_new') {
            $this->handleMessage($object['message']);
        }
        
        return response('ok');
    }
    
    protected function handleMessage($message)
    {
        $peerId = $message['peer_id'];
        $text = $message['text'] ?? '';
        
        if ($text === 'начать' || $text === '/start') {
            $keyboard = vk_keyboard()
                ->row()
                ->openApp('🎰 Открыть рулетку', config('vk.app_id'), -config('vk.group_id'))
                ->get();
            
            vk_send($peerId, '👋 Добро пожаловать в WOW Рулетку!', [
                'keyboard' => $keyboard
            ]);
        }
    }
}
```

### 2. Long Poll обработчик

```php
<?php

namespace App\Console\Commands;

use App\VK\LongPoll;
use App\VK\VK;
use Illuminate\Console\Command;

class VKLongPollCommand extends Command
{
    protected $signature = 'vk:longpoll';
    protected $description = 'Запустить VK Long Poll';

    public function handle()
    {
        $this->info('VK Long Poll started...');
        
        $longPoll = new LongPoll();
        
        $longPoll->listen(function ($update) {
            $type = $update['type'] ?? '';
            $object = $update['object'] ?? [];
            
            if ($type === 'message_new') {
                $message = $object['message'] ?? [];
                $peerId = $message['peer_id'] ?? 0;
                $text = $message['text'] ?? '';
                
                if ($text === 'привет') {
                    vk_send($peerId, 'Привет! 👋');
                }
            }
        });
    }
}
```

### 3. VK Mini App аутентификация

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VKUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('vk.auth');
    }
    
    public function getProfile(Request $request)
    {
        $vkUserId = $request->vk_user_id;
        $vkParams = $request->vk_params;
        
        $user = User::firstOrCreate(
            ['vk_user_id' => $vkUserId],
            [
                'name' => 'VK User ' . $vkUserId,
            ]
        );
        
        return response()->json($user);
    }
}
```

### 4. Работа с сообществом

```php
<?php

namespace App\Services;

use App\VK\Community;

class VKCommunityService
{
    protected Community $community;

    public function __construct()
    {
        $this->community = new Community();
    }

    public function getCommunityInfo()
    {
        return $this->community->getInfo([
            'description',
            'members_count',
            'activity',
        ]);
    }

    public function getMembers(int $offset = 0)
    {
        return $this->community->getMembers($offset, 1000, [
            'photo_200',
            'city',
        ]);
    }

    public function isMember(int $userId): bool
    {
        return $this->community->isMember($userId);
    }

    public function setupCallback(string $url)
    {
        // Добавляем сервер
        $this->community->addCallbackServer($url, 'Laravel Bot', config('vk.callback.secret'));
        
        // Включаем все события
        $this->community->enableAllCallbackEvents();
        
        // Получаем код подтверждения
        return $this->community->getConfirmationCode();
    }

    public function banUser(int $userId, string $reason)
    {
        return $this->community->banUser($userId, null, $reason);
    }
}
```

### 5. Сервис уведомлений

```php
<?php

namespace App\Services;

use App\VK\VK;
use App\Jobs\VK\SendMessageJob;

class VKNotificationService
{
    public function notifyNewTicket($user)
    {
        $keyboard = vk_keyboard()
            ->row()
            ->openApp('🎰 Крутить рулетку', config('vk.app_id'), -config('vk.group_id'))
            ->get();
        
        SendMessageJob::dispatch(
            $user->vk_user_id,
            "🎫 Новый билет!\n\nУ вас восстановился билет для вращения рулетки!",
            ['keyboard' => $keyboard]
        );
    }
    
    public function notifyWin($user, $amount)
    {
        $keyboard = vk_keyboard()
            ->row()
            ->openApp('🎉 Забрать приз', config('vk.app_id'), -config('vk.group_id'))
            ->get();
        
        vk_send(
            $user->vk_user_id,
            "🎉 Поздравляем!\n\nВы выиграли {$amount}₽!",
            ['keyboard' => $keyboard]
        );
    }
}
```

### 6. Массовая рассылка

```php
<?php

namespace App\Console\Commands;

use App\Jobs\VK\SendBroadcastJob;
use Illuminate\Console\Command;

class VKBroadcastCommand extends Command
{
    protected $signature = 'vk:broadcast {message}';
    protected $description = 'Отправить сообщение всем пользователям VK';

    public function handle()
    {
        $message = $this->argument('message');
        
        $keyboard = vk_keyboard()
            ->row()
            ->openApp('Открыть приложение', config('vk.app_id'), -config('vk.group_id'))
            ->get();
        
        SendBroadcastJob::dispatch($message, ['keyboard' => $keyboard]);
        
        $this->info('✓ Рассылка запущена!');
    }
}
```

### 7. Загрузка и отправка фото

```php
<?php

namespace App\Services;

use App\VK\VK;

class VKPhotoService
{
    public function sendPhoto(int $peerId, string $photoPath, string $caption = '')
    {
        $bot = VK::bot();
        
        // 1. Получаем URL для загрузки
        $uploadServer = $bot->getPhotoUploadServer($peerId);
        $uploadUrl = $uploadServer['upload_url'];
        
        // 2. Загружаем файл
        $uploaded = $bot->upload($uploadUrl, 'photo', $photoPath);
        
        // 3. Сохраняем фото
        $saved = $bot->saveMessagesPhoto(
            $uploaded['photo'],
            $uploaded['server'],
            $uploaded['hash']
        );
        
        // 4. Отправляем сообщение с фото
        $photo = $saved[0];
        $attachment = vk_attachment('photo', $photo['owner_id'], $photo['id'], $photo['access_key'] ?? '');
        
        return $bot->sendMessage($peerId, $caption, [
            'attachment' => $attachment
        ]);
    }
}
```

### 8. Работа с Storage

```php
<?php

namespace App\Services;

use App\VK\VK;

class VKStorageService
{
    public function saveUserData(int $userId, string $key, mixed $value)
    {
        return VK::bot()->storageSet($key, json_encode($value), $userId);
    }
    
    public function getUserData(int $userId, array $keys)
    {
        $result = VK::bot()->storageGet($keys, $userId);
        
        $data = [];
        foreach ($result as $item) {
            $data[$item['key']] = json_decode($item['value'], true);
        }
        
        return $data;
    }
}
```

## Обработка ошибок

```php
use App\VK\Exceptions\VKException;
use App\VK\Exceptions\VKValidationException;

// Обработка ошибок API
try {
    vk_send(123456789, 'Hello');
} catch (VKException $e) {
    $message = $e->getMessage();
    
    // Проверяем конкретные ошибки
    if (str_contains($message, '[901]')) {
        // Пользователь запретил сообщения
        Log::info('User disabled messages', ['user_id' => 123456789]);
    } elseif (str_contains($message, '[6]')) {
        // Слишком много запросов
        sleep(1);
        // Повторить запрос
    }
    
    Log::error('VK API error: ' . $e->getMessage());
}

// Обработка ошибок валидации
try {
    $keyboard = vk_keyboard()
        ->row()
        ->text(str_repeat('A', 50), 'btn'); // Слишком длинный текст
} catch (VKValidationException $e) {
    Log::error('Validation error: ' . $e->getMessage());
}
```

## Настройка в routes/api.php

```php
use App\Http\Controllers\Api\VKCallbackController;

// Callback API (защищен middleware)
Route::post('/vk/callback', [VKCallbackController::class, 'handle'])
    ->middleware('vk.callback');

// API для Mini App (требует аутентификации)
Route::middleware('vk.auth')->prefix('vk')->group(function () {
    Route::get('/user/profile', [VKUserController::class, 'getProfile']);
    Route::post('/wheel/spin', [WheelController::class, 'spin']);
});

// Admin API (требует права администратора)
Route::middleware(['vk.auth', 'vk.admin'])->prefix('vk/admin')->group(function () {
    Route::post('/broadcast', [AdminController::class, 'broadcast']);
});
```

## 📖 Дополнительно

- [README.md](README.md) - Основная документация
- [SETUP.md](SETUP.md) - Установка и настройка
- [LIMITS.md](LIMITS.md) - Лимиты и валидация
- [FEATURES.md](FEATURES.md) - Все возможности
- [VK Dev Portal](https://dev.vk.com/api/bots)

