<?php

namespace LetoceilingCoder\VK\Types;

/**
 * Представляет беседу VK
 */
class Chat
{
    public int $id;
    public string $type;
    public ?string $title = null;
    public ?int $adminId = null;
    public ?array $users = null;

    public static function fromArray(array $data): self
    {
        $chat = new self();
        $chat->id = $data['id'];
        $chat->type = $data['type'];
        $chat->title = $data['title'] ?? null;
        $chat->adminId = $data['admin_id'] ?? null;
        $chat->users = $data['users'] ?? null;
        return $chat;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'admin_id' => $this->adminId,
            'users' => $this->users,
        ], fn($value) => $value !== null);
    }
}

