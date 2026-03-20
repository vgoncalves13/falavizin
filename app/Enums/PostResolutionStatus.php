<?php

namespace App\Enums;

enum PostResolutionStatus: string
{
    case EmAndamento = 'em_andamento';
    case Resolvido = 'resolvido';

    public function label(): string
    {
        return match ($this) {
            self::EmAndamento => 'Em andamento',
            self::Resolvido => 'Resolvido',
        };
    }
}
