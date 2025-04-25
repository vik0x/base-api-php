<?php

namespace Src\Infrastructure\Bus;

interface Middleware
{
    /**
     * @return mixed
     */
    public function execute(object $command, callable $next): mixed;
}
