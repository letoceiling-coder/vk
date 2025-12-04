<?php

namespace LetoceilingCoder\VK;

use LetoceilingCoder\VK\VKClient;
use LetoceilingCoder\VK\Validator;

/**
 * Класс для работы с VK Bot API
 * Документация: https://dev.vk.com/api/bots
 */
class Bot extends VKClient
{
    // ==========================================
    // Messages (Сообщения)
    // ==========================================

    /**
     * Отправить сообщение
     * messages.send
     * 
     * @param int|string $userId - ID пользователя или peer_id
     * @param string $message - Текст сообщения
     * @param array $params - Дополнительные параметры
     */
    public function sendMessage(int|string $userId, string $message = '', array $params = []): array
    {
        // Валидация
        if ($message) {
            Validator::validateMessageText($message);
        }

        $data = array_merge([
            'peer_id' => $userId,
            'message' => $message,
            'random_id' => $this->getRandomId(),
        ], $params);

        return $this->call('messages.send', $data);
    }

    /**
     * Редактировать сообщение
     * messages.edit
     */
    public function editMessage(int $peerId, int $conversationMessageId, string $message, array $params = []): array
    {
        Validator::validateMessageText($message);

        $data = array_merge([
            'peer_id' => $peerId,
            'conversation_message_id' => $conversationMessageId,
            'message' => $message,
        ], $params);

        return $this->call('messages.edit', $data);
    }

    /**
     * Удалить сообщение
     * messages.delete
     */
    public function deleteMessage(array $messageIds, bool $deleteForAll = false): array
    {
        return $this->call('messages.delete', [
            'message_ids' => implode(',', $messageIds),
            'delete_for_all' => $deleteForAll ? 1 : 0,
        ]);
    }

    /**
     * Получить сообщения по ID
     * messages.getById
     */
    public function getMessagesById(array $messageIds): array
    {
        return $this->call('messages.getById', [
            'message_ids' => implode(',', $messageIds),
        ]);
    }

    /**
     * Получить историю сообщений
     * messages.getHistory
     */
    public function getHistory(int $userId, int $offset = 0, int $count = 20): array
    {
        return $this->call('messages.getHistory', [
            'user_id' => $userId,
            'offset' => $offset,
            'count' => $count,
        ]);
    }

    /**
     * Получить диалоги
     * messages.getConversations
     */
    public function getConversations(int $offset = 0, int $count = 20): array
    {
        return $this->call('messages.getConversations', [
            'offset' => $offset,
            'count' => $count,
        ]);
    }

    /**
     * Отметить сообщения как прочитанные
     * messages.markAsRead
     */
    public function markAsRead(int $peerId, ?int $startMessageId = null): array
    {
        $params = ['peer_id' => $peerId];
        
        if ($startMessageId) {
            $params['start_message_id'] = $startMessageId;
        }

        return $this->call('messages.markAsRead', $params);
    }

    /**
     * Отправить действие (typing, audiomessage)
     * messages.setActivity
     */
    public function sendActivity(int $userId, string $type = 'typing'): array
    {
        return $this->call('messages.setActivity', [
            'user_id' => $userId,
            'type' => $type,
        ]);
    }

    // ==========================================
    // Users (Пользователи)
    // ==========================================

    /**
     * Получить информацию о пользователях
     * users.get
     */
    public function getUsers(array $userIds, array $fields = []): array
    {
        return $this->call('users.get', [
            'user_ids' => implode(',', $userIds),
            'fields' => implode(',', $fields),
        ]);
    }

    /**
     * Получить информацию об одном пользователе
     */
    public function getUser(int $userId, array $fields = []): ?array
    {
        $users = $this->getUsers([$userId], $fields);
        return $users[0] ?? null;
    }

    // ==========================================
    // Groups (Сообщества)
    // ==========================================

    /**
     * Получить информацию о сообществе
     * groups.getById
     */
    public function getGroup(?int $groupId = null, array $fields = []): array
    {
        $params = ['fields' => implode(',', $fields)];
        
        if ($groupId) {
            $params['group_id'] = $groupId;
        }

        return $this->call('groups.getById', $params);
    }

    /**
     * Получить участников сообщества
     * groups.getMembers
     */
    public function getGroupMembers(int $groupId, int $offset = 0, int $count = 1000): array
    {
        return $this->call('groups.getMembers', [
            'group_id' => $groupId,
            'offset' => $offset,
            'count' => $count,
        ]);
    }

    /**
     * Проверить, является ли пользователь участником сообщества
     * groups.isMember
     */
    public function isGroupMember(int $groupId, int $userId): bool
    {
        $result = $this->call('groups.isMember', [
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);

        return $result === 1;
    }

    // ==========================================
    // Photos (Фотографии)
    // ==========================================

    /**
     * Получить URL для загрузки фотографии в сообщение
     * photos.getMessagesUploadServer
     */
    public function getPhotoUploadServer(int $peerId): array
    {
        return $this->call('photos.getMessagesUploadServer', [
            'peer_id' => $peerId,
        ]);
    }

    /**
     * Сохранить фотографию после загрузки
     * photos.saveMessagesPhoto
     */
    public function saveMessagesPhoto(string $photo, string $server, string $hash): array
    {
        return $this->call('photos.saveMessagesPhoto', [
            'photo' => $photo,
            'server' => $server,
            'hash' => $hash,
        ]);
    }

    // ==========================================
    // Docs (Документы)
    // ==========================================

    /**
     * Получить URL для загрузки документа
     * docs.getMessagesUploadServer
     */
    public function getDocsUploadServer(int $peerId, string $type = 'doc'): array
    {
        return $this->call('docs.getMessagesUploadServer', [
            'peer_id' => $peerId,
            'type' => $type,
        ]);
    }

    /**
     * Сохранить документ после загрузки
     * docs.save
     */
    public function saveDoc(string $file, string $title = '', string $tags = ''): array
    {
        return $this->call('docs.save', [
            'file' => $file,
            'title' => $title,
            'tags' => $tags,
        ]);
    }

    // ==========================================
    // Utils (Утилиты)
    // ==========================================

    /**
     * Получить короткую ссылку
     * utils.getShortLink
     */
    public function getShortLink(string $url): array
    {
        return $this->call('utils.getShortLink', [
            'url' => $url,
        ]);
    }

    /**
     * Проверить ссылку
     * utils.checkLink
     */
    public function checkLink(string $url): array
    {
        return $this->call('utils.checkLink', [
            'url' => $url,
        ]);
    }

    // ==========================================
    // Callback API
    // ==========================================

    /**
     * Получить настройки Callback API
     * groups.getCallbackSettings
     */
    public function getCallbackSettings(int $groupId): array
    {
        return $this->call('groups.getCallbackSettings', [
            'group_id' => $groupId,
        ]);
    }

    /**
     * Установить настройки Callback API
     * groups.setCallbackSettings
     */
    public function setCallbackSettings(int $groupId, array $settings): array
    {
        $params = array_merge(['group_id' => $groupId], $settings);
        return $this->call('groups.setCallbackSettings', $params);
    }

    /**
     * Получить информацию о Callback сервере
     * groups.getCallbackServers
     */
    public function getCallbackServers(int $groupId): array
    {
        return $this->call('groups.getCallbackServers', [
            'group_id' => $groupId,
        ]);
    }

    /**
     * Добавить Callback сервер
     * groups.addCallbackServer
     */
    public function addCallbackServer(int $groupId, string $url, string $title, string $secretKey = ''): array
    {
        return $this->call('groups.addCallbackServer', [
            'group_id' => $groupId,
            'url' => $url,
            'title' => $title,
            'secret_key' => $secretKey,
        ]);
    }

    /**
     * Удалить Callback сервер
     * groups.deleteCallbackServer
     */
    public function deleteCallbackServer(int $groupId, int $serverId): array
    {
        return $this->call('groups.deleteCallbackServer', [
            'group_id' => $groupId,
            'server_id' => $serverId,
        ]);
    }

    /**
     * Получить код подтверждения для Callback API
     * groups.getCallbackConfirmationCode
     */
    public function getCallbackConfirmationCode(int $groupId): array
    {
        return $this->call('groups.getCallbackConfirmationCode', [
            'group_id' => $groupId,
        ]);
    }

    // ==========================================
    // Storage (Хранилище)
    // ==========================================

    /**
     * Получить данные из хранилища
     * storage.get
     */
    public function storageGet(array $keys, ?int $userId = null): array
    {
        $params = ['keys' => implode(',', $keys)];
        
        if ($userId) {
            $params['user_id'] = $userId;
        }

        return $this->call('storage.get', $params);
    }

    /**
     * Сохранить данные в хранилище
     * storage.set
     */
    public function storageSet(string $key, string $value, ?int $userId = null): array
    {
        $params = [
            'key' => $key,
            'value' => $value,
        ];
        
        if ($userId) {
            $params['user_id'] = $userId;
        }

        return $this->call('storage.set', $params);
    }
}

