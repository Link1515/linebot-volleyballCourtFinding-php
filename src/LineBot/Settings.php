<?php

declare(strict_types=1);

namespace TerryLin\LineBot;

final class Settings
{
    public function __construct(private readonly array $data)
    {
    }

    public function get(string $key, mixed $default = ''): mixed
    {
        $parts = explode('.', $key);
        $value = $this->data;

        foreach ($parts as $part) {
            if (!isset($value[$part]) || !\array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
