<?php

return [
        'default'     => 'pgsql',
        'connections' => [
                          'pgsql' => [
                                      'driver'   => 'pdo_pgsql',
                                      'host'     => $_ENV['DB_HOST'],
                                      'port'     => $_ENV['DB_PORT'],
                                      'dbname'   => $_ENV['DB_DATABASE'],
                                      'user'     => $_ENV['DB_USERNAME'],
                                      'password' => $_ENV['DB_PASSWORD'],
                                      'charset'  => 'utf8',
                                     ],
                         ],
       ];
