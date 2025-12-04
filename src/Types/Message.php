<?php

namespace LetoceilingCoder\VK\Types;

/**
 * Представляет сообщение VK
 */
class Message
{
    public int $id;
    public int $peerId;
    public int $fromId;
    public int $date;
    public string $text;
    public ?array $attachments = null;
    public ?array $fwdMessages = null;
    public ?array $keyboard = null;

    public static function fromArray(array $data): self
    {
        $message = new self();
        $message->id = $data['id'];
        $message->peerId = $data['peer_id'];
        $message->fromId = $data['from_id'];
        $message->date = $data['date'];
        $message->text = $data['text'] ?? '';
        $message->attachments = $data['attachments'] ?? null;
        $message->fwdMessages = $data['fwd_messages'] ?? null;
        $message->keyboard = $data['keyboard'] ?? null;
        return $message;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'peer_id' => $this->peerId,
            'from_id' => $this->fromId,
            'date' => $this->date,
            'text' => $this->text,
            'attachments' => $this->attachments,
            'fwd_messages' => $this->fwdMessages,
            'keyboard' => $this->keyboard,
        ], fn($value) => $value !== null);
    }
}

