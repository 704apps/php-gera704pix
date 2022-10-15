<?php

namespace Apps704\Gera704pix\Exceptions;

class TamanhoMaximoDeCaracteresException extends Gera704PixException
{
    public function __construct(string $tx)
    {
        parent::__construct("Tamanho máximo deve ser 99, inválido: $tx possui " . strlen($tx) . " caracteres.");
    }
}