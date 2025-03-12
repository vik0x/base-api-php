<?php

namespace Src\Infrastructure\Bus;

interface Middleware
{
    public function execute($command, callable $next);
}
