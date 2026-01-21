<?php

declare(strict_types=1);

namespace TerryLin\LineBot\Handler;

interface HandlerInterface
{
    public function getReplyMessages(): array;
}
