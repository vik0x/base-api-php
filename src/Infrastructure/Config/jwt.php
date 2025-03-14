<?php

return [
        'secret'     => $_ENV['JWT_SECRET'],
        'expiration' => $_ENV['JWT_EXPIRATION'] ?? 3600, // 1 hour
       ];
