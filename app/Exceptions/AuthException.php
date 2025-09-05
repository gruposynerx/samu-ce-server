<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthException extends HttpException
{
    public static function invalidCredentials(): self
    {
        return new self(Response::HTTP_UNAUTHORIZED, 'Atenção! Usuário ou senha inválidos.');
    }

    public static function userInactive(): self
    {
        return new self(Response::HTTP_UNAUTHORIZED, 'Seu usuário está inativo! Por favor, entre em contato com a administração.');
    }

    public static function inactivityExpiration(): self
    {
        return new self(Response::HTTP_UNAUTHORIZED, 'Sessão expirada por inatividade.');
    }

    public static function invalidDevice(): self
    {
        return new self(Response::HTTP_NOT_ACCEPTABLE, 'Dispositivo móvel alterado, por favor confirme o PIN novamente.');
    }

    public static function doesntHaveSelectedRole(): self
    {
        return new self(Response::HTTP_NOT_ACCEPTABLE, 'Você não possui o perfil selecionado, por favor contate o administrador.');
    }

    public static function mobileAccessNotAllowed(): self
    {
        return new self(Response::HTTP_UNAUTHORIZED, 'Acesso por dispositivos móveis bloqueado.');
    }

    public static function pushTokenError(): self
    {
        return new self(Response::HTTP_UNAUTHORIZED, 'Erro ao registrar o token de notificação.');
    }
}
