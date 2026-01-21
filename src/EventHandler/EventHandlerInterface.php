<?php

declare(strict_types=1);

namespace TerryLin\LineBot\EventHandler;

interface EventHandlerInterface
{
    public function getReplyMessages(): array;
}
