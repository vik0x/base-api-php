<?php

return [
        'secret'     => isset($_ENV['JWT_SECRET']) ? $_ENV['JWT_SECRET'] : throw new \RuntimeException('JWT_SECRET is not set'),
        'expiration' => (int) ($_ENV['JWT_EXPIRATION'] ?? 3600), // 1 hour
       ];
