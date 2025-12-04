<?php

namespace LetoceilingCoder\VK\Types;

/**
 * Представляет пользователя VK
 */
class User
{
    public int $id;
    public string $firstName;
    public string $lastName;
    public ?string $photo = null;
    public ?string $screenName = null;
    public ?int $sex = null;
    public ?string $bdate = null;
    public ?string $city = null;
    public ?string $country = null;

    public static function fromArray(array $data): self
    {
        $user = new self();
        $user->id = $data['id'];
        $user->firstName = $data['first_name'];
        $user->lastName = $data['last_name'];
        $user->photo = $data['photo_200'] ?? $data['photo_100'] ?? null;
        $user->screenName = $data['screen_name'] ?? null;
        $user->sex = $data['sex'] ?? null;
        $user->bdate = $data['bdate'] ?? null;
        $user->city = $data['city']['title'] ?? null;
        $user->country = $data['country']['title'] ?? null;
        return $user;
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'photo' => $this->photo,
            'screen_name' => $this->screenName,
            'sex' => $this->sex,
            'bdate' => $this->bdate,
            'city' => $this->city,
            'country' => $this->country,
        ], fn($value) => $value !== null);
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }
}

