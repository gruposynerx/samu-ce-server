<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AttendanceException extends HttpException
{
    public static function alreadyInAttendance(string $responsible, string $occurrenceNumber): self
    {
        $message = "A ocorrência nº $occurrenceNumber já está em atendimento pelo usuário: $responsible";

        return new self(Response::HTTP_CONFLICT, mb_strtoupper($message, 'utf8'));
    }

    public static function alreadyFinished(): self
    {
        return new self(Response::HTTP_CONFLICT, 'O Atendimento já foi concluído.');
    }
}
