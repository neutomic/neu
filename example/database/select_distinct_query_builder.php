<?php

declare(strict_types=1);

use Neu\Database\OrderDirection;
use Psl\SecureRandom;
use Psl\PseudoRandom;
use Psl\IO;
use Psl\Vec;

$database = require __DIR__ . '/bootstrap.php';

$database->query('DROP TABLE IF EXISTS users');

$database->query('CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(256) NOT NULL,
    country VARCHAR(256) NOT NULL
)');

$values = [];
$parameters = [];
foreach (['Tunisia', 'France', 'Spain', 'Algeria', 'Egypt', 'United States of America', 'China', 'Japan', 'Brazil', 'South Africa'] as $country) {
    // produce between 10, to 40 users with unique usernames for the current country.
    foreach (Vec\reproduce(PseudoRandom\int(10, 40), static fn() => SecureRandom\string(8)) as $i => $username) {
        $values[] = ['username' => ':username' . $i, 'country' => ':country' . $i];
        $parameters['username'.$i] = $username;
        $parameters['country'.$i] = $country;
    }
}

$database
    ->createQueryBuilder()
    ->insert('users')
    ->values(...$values)
    ->execute($parameters)
;

$countries = $database
    ->createQueryBuilder()
    ->select('u.country')
    ->from('users', 'u')
    ->orderBy('u.country', OrderDirection::Descending)
    ->distinct()
    ->execute()
    ->getRows()
;

foreach ($countries as ['country' => $country]) {
    IO\write_line('- %s', $country);
}
