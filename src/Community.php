<?php

namespace LetoceilingCoder\VK;

use LetoceilingCoder\VK\VKClient;

/**
 * Класс для удобной работы с сообществами VK
 * Документация: https://dev.vk.com/api/community-messages
 */
class Community extends VKClient
{
    protected int $groupId;

    public function __construct(?string $accessToken = null, ?int $groupId = null)
    {
        parent::__construct($accessToken);
        $this->groupId = $groupId ?? config('vk.group_id');
    }

    // ==========================================
    // Информация о сообществе
    // ==========================================

    /**
     * Получить информацию о сообществе
     */
    public function getInfo(array $fields = []): array
    {
        return $this->call('groups.getById', [
            'group_id' => $this->groupId,
            'fields' => implode(',', $fields),
        ]);
    }

    /**
     * Получить участников сообщества
     */
    public function getMembers(int $offset = 0, int $count = 1000, array $fields = []): array
    {
        return $this->call('groups.getMembers', [
            'group_id' => $this->groupId,
            'offset' => $offset,
            'count' => $count,
            'fields' => implode(',', $fields),
        ]);
    }

    /**
     * Проверить, является ли пользователь участником
     */
    public function isMember(int $userId): bool
    {
        $result = $this->call('groups.isMember', [
            'group_id' => $this->groupId,
            'user_id' => $userId,
        ]);

        return $result === 1;
    }

    /**
     * Получить администраторов сообщества
     */
    public function getAdmins(): array
    {
        $members = $this->call('groups.getMembers', [
            'group_id' => $this->groupId,
            'filter' => 'managers',
        ]);

        return $members['items'] ?? [];
    }

    // ==========================================
    // Callback API
    // ==========================================

    /**
     * Получить настройки Callback API
     */
    public function getCallbackSettings(): array
    {
        return $this->call('groups.getCallbackSettings', [
            'group_id' => $this->groupId,
        ]);
    }

    /**
     * Установить настройки Callback API
     */
    public function setCallbackSettings(array $settings): array
    {
        $params = array_merge(['group_id' => $this->groupId], $settings);
        return $this->call('groups.setCallbackSettings', $params);
    }

    /**
     * Включить все события Callback API
     */
    public function enableAllCallbackEvents(): array
    {
        return $this->setCallbackSettings([
            'message_new' => 1,
            'message_reply' => 1,
            'message_edit' => 1,
            'message_allow' => 1,
            'message_deny' => 1,
            'message_typing_state' => 1,
            'photo_new' => 1,
            'audio_new' => 1,
            'video_new' => 1,
            'wall_post_new' => 1,
            'wall_repost' => 1,
            'board_post_new' => 1,
            'board_post_edit' => 1,
            'board_post_restore' => 1,
            'board_post_delete' => 1,
            'photo_comment_new' => 1,
            'video_comment_new' => 1,
            'market_comment_new' => 1,
            'group_join' => 1,
            'group_leave' => 1,
            'user_block' => 1,
            'user_unblock' => 1,
            'poll_vote_new' => 1,
            'group_officers_edit' => 1,
            'group_change_settings' => 1,
            'group_change_photo' => 1,
            'vkpay_transaction' => 1,
            'app_payload' => 1,
        ]);
    }

    /**
     * Получить Callback серверы
     */
    public function getCallbackServers(): array
    {
        return $this->call('groups.getCallbackServers', [
            'group_id' => $this->groupId,
        ]);
    }

    /**
     * Добавить Callback сервер
     */
    public function addCallbackServer(string $url, string $title, string $secretKey = ''): array
    {
        return $this->call('groups.addCallbackServer', [
            'group_id' => $this->groupId,
            'url' => $url,
            'title' => $title,
            'secret_key' => $secretKey,
        ]);
    }

    /**
     * Удалить Callback сервер
     */
    public function deleteCallbackServer(int $serverId): array
    {
        return $this->call('groups.deleteCallbackServer', [
            'group_id' => $this->groupId,
            'server_id' => $serverId,
        ]);
    }

    /**
     * Получить код подтверждения
     */
    public function getConfirmationCode(): string
    {
        $result = $this->call('groups.getCallbackConfirmationCode', [
            'group_id' => $this->groupId,
        ]);

        return $result['code'] ?? '';
    }

    // ==========================================
    // Сообщения от имени сообщества
    // ==========================================

    /**
     * Отправить сообщение от имени сообщества
     */
    public function sendMessage(int|string $userId, string $message, array $params = []): array
    {
        Validator::validateMessageText($message);

        $data = array_merge([
            'peer_id' => $userId,
            'message' => $message,
            'random_id' => $this->getRandomId(),
        ], $params);

        return $this->call('messages.send', $data);
    }

    /**
     * Получить диалоги сообщества
     */
    public function getConversations(int $offset = 0, int $count = 20): array
    {
        return $this->call('messages.getConversations', [
            'offset' => $offset,
            'count' => $count,
            'group_id' => $this->groupId,
        ]);
    }

    /**
     * Заблокировать пользователя в сообществе
     */
    public function banUser(int $userId, ?int $endDate = null, string $reason = '', string $comment = ''): array
    {
        $params = [
            'group_id' => $this->groupId,
            'owner_id' => $userId,
        ];

        if ($endDate) {
            $params['end_date'] = $endDate;
        }

        if ($reason) {
            $params['reason'] = $reason;
        }

        if ($comment) {
            $params['comment'] = $comment;
        }

        return $this->call('groups.ban', $params);
    }

    /**
     * Разблокировать пользователя в сообществе
     */
    public function unbanUser(int $userId): array
    {
        return $this->call('groups.unban', [
            'group_id' => $this->groupId,
            'owner_id' => $userId,
        ]);
    }

    /**
     * Получить список забаненных пользователей
     */
    public function getBanned(int $offset = 0, int $count = 20): array
    {
        return $this->call('groups.getBanned', [
            'group_id' => $this->groupId,
            'offset' => $offset,
            'count' => $count,
        ]);
    }

    // ==========================================
    // Статистика сообщества
    // ==========================================

    /**
     * Получить статистику сообщества
     */
    public function getStats(string $dateFrom, string $dateTo): array
    {
        return $this->call('stats.get', [
            'group_id' => $this->groupId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);
    }

    // ==========================================
    // Вспомогательные методы
    // ==========================================

    /**
     * Установить ID группы
     */
    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * Получить ID группы
     */
    public function getGroupId(): int
    {
        return $this->groupId;
    }
}

