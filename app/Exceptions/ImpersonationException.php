<?php

namespace App\Exceptions;

class ImpersonationException extends \Exception
{
    public static function notAuthorized(): self
    {
        return new self('Apenas administradores podem impersonar usuários.', 403);
    }

    public static function cannotImpersonateAdmin(): self
    {
        return new self('Não é possível impersonar outro administrador.', 403);
    }

    public static function cannotImpersonateSelf(): self
    {
        return new self('Você não pode impersonar a si mesmo.', 403);
    }

    public static function alreadyImpersonating(): self
    {
        return new self('Já existe uma impersonação ativa. Encerre-a antes de iniciar outra.', 409);
    }

    public static function notImpersonating(): self
    {
        return new self('Nenhuma impersonação ativa para encerrar.', 400);
    }
}
