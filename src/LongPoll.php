<?php

namespace LetoceilingCoder\VK;

use LetoceilingCoder\VK\VKClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Класс для работы с Bots Long Poll API
 * Документация: https://dev.vk.com/api/bots/getting-started
 */
class LongPoll extends VKClient
{
    protected int $groupId;
    protected ?string $server = null;
    protected ?string $key = null;
    protected int $ts = 0;
    protected int $wait = 25;

    public function __construct(?string $accessToken = null, ?int $groupId = null)
    {
        parent::__construct($accessToken);
        $this->groupId = $groupId ?? config('vk.group_id');
    }

    /**
     * Получить сервер Long Poll
     */
    public function getServer(): array
    {
        $response = $this->call('groups.getLongPollServer', [
            'group_id' => $this->groupId,
        ]);

        $this->server = $response['server'] ?? null;
        $this->key = $response['key'] ?? null;
        $this->ts = $response['ts'] ?? 0;

        return $response;
    }

    /**
     * Получить обновления
     * 
     * @return array
     */
    public function getUpdates(): array
    {
        if (!$this->server || !$this->key) {
            $this->getServer();
        }

        try {
            $url = "{$this->server}?act=a_check&key={$this->key}&ts={$this->ts}&wait={$this->wait}";

            $response = Http::timeout($this->wait + 5)->get($url);
            $data = $response->json();

            // Обновляем ts
            if (isset($data['ts'])) {
                $this->ts = $data['ts'];
            }

            // Обработка ошибок Long Poll
            if (isset($data['failed'])) {
                $failed = $data['failed'];
                
                if ($failed === 1) {
                    // История устарела, обновляем ts
                    $this->ts = $data['ts'];
                    return [];
                } elseif (in_array($failed, [2, 3])) {
                    // Ключ устарел, получаем новый сервер
                    $this->getServer();
                    return [];
                }
            }

            return $data['updates'] ?? [];

        } catch (\Exception $e) {
            Log::error('VK Long Poll error: ' . $e->getMessage());
            
            // При ошибке переподключаемся
            $this->getServer();
            
            return [];
        }
    }

    /**
     * Запустить Long Poll в бесконечном цикле
     * 
     * @param callable $callback - Функция обработки обновлений
     */
    public function listen(callable $callback): void
    {
        $this->log('VK Long Poll started', ['group_id' => $this->groupId]);

        while (true) {
            try {
                $updates = $this->getUpdates();

                foreach ($updates as $update) {
                    try {
                        $callback($update);
                    } catch (\Exception $e) {
                        Log::error('Error processing VK update', [
                            'error' => $e->getMessage(),
                            'update' => $update,
                        ]);
                    }
                }

            } catch (\Exception $e) {
                Log::error('VK Long Poll listen error: ' . $e->getMessage());
                sleep(5); // Задержка перед повторной попыткой
            }
        }
    }

    /**
     * Установить время ожидания (wait)
     */
    public function setWait(int $seconds): self
    {
        $this->wait = min(90, max(1, $seconds)); // От 1 до 90 секунд
        return $this;
    }

    /**
     * Установить ID группы
     */
    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * Получить текущий ts
     */
    public function getTs(): int
    {
        return $this->ts;
    }
}

