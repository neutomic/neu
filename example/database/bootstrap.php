<?php

declare(strict_types=1);

use Amp\Postgres;
use Neu\Database\Bridge\Postgres\Database;

require __DIR__ . '/../../vendor/autoload.php';

const DATABASE_DEBUG_QUERIES = 1;

return new Database(
    Postgres\connect(Postgres\PostgresConfig::fromString('host=127.0.0.1 port=5432 user=main password=main'))
);
