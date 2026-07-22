<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Moderator = 'moderator';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Usuário',
            self::Moderator => 'Moderador',
            self::Admin => 'Administrador',
        };
    }

    public function canModerate(): bool
    {
        return $this === self::Moderator || $this === self::Admin;
    }
}
