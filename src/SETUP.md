# Установка и настройка VK API для Laravel

## 🚀 Быстрая установка

### 1. Регистрация Service Provider

Добавьте в `bootstrap/providers.php` (Laravel 11):

```php
return [
    App\Providers\VKServiceProvider::class,
];
```

### 2. Настройка .env

```env
VK_ACCESS_TOKEN=your_access_token_here
VK_SECRET_KEY=your_secret_key_here
VK_GROUP_ID=your_group_id
VK_API_VERSION=5.131

# Callback API
VK_CALLBACK_URL="${APP_URL}/api/vk/callback"
VK_CALLBACK_SECRET=random_secret_string
VK_CONFIRMATION_CODE=confirmation_code_from_vk

# VK Mini App
VK_APP_ID=your_vk_app_id
VK_MINI_APP_URL="${APP_URL}"

# Admin IDs
VK_ADMIN_IDS=123456789,987654321
```

### 3. Загрузка helper функций

В `composer.json` добавьте:

```json
"autoload": {
    "files": [
        "app/VK/helpers.php"
    ]
}
```

Затем:

```bash
composer dump-autoload
```

### 4. Регистрация Middleware

В `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'vk.auth' => \App\Http\Middleware\VKAuth::class,
        'vk.callback' => \App\Http\Middleware\VKCallback::class,
        'vk.admin' => \App\Http\Middleware\VKAdmin::class,
    ]);
})
```

### 5. Настройка Callback API

```bash
# Установить callback сервер
php artisan vk:set-callback

# Проверить статус
php artisan vk:callback-info

# Удалить callback (указать server_id)
php artisan vk:delete-callback 123

# Тест подключения
php artisan vk:test
```

## 📚 Использование

### Helper функции

```php
// Отправить сообщение
vk_send(123456789, 'Привет!');

// Валидировать Mini App
$userId = vk_get_user_id($queryString);

// Создать клавиатуру
$keyboard = vk_keyboard()
    ->row()
    ->text('Кнопка', 'payload')
    ->get();
```

### Routes

```php
// Callback API
Route::post('/api/vk/callback', [VKCallbackController::class, 'handle'])
    ->middleware('vk.callback');

// Mini App API
Route::middleware('vk.auth')->prefix('vk')->group(function () {
    Route::get('/user/profile', [UserController::class, 'getProfile']);
});

// Admin API
Route::middleware(['vk.auth', 'vk.admin'])->group(function () {
    Route::post('/admin/broadcast', [AdminController::class, 'broadcast']);
});
```

## 🎯 Artisan команды

```bash
php artisan vk:test              # Проверка подключения
php artisan vk:set-callback      # Установка callback
php artisan vk:callback-info     # Информация о callback
php artisan vk:delete-callback   # Удаление callback
```

## 📖 Документация

- [README.md](README.md) - Основная документация
- [LIMITS.md](LIMITS.md) - Лимиты и валидация
- [Официальная документация VK](https://dev.vk.com/api/bots)

