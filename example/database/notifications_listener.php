<?php

use Psl\{IO, Async};

$database = require __DIR__ . '/bootstrap.php';

$listener = $database->getListener('test');

Async\Scheduler::onSignal(SIGINT, $listener->close(...));

foreach ($listener->listen() as $notification) {
    IO\write_line('notification received from process #%d: "%s"', $notification->pid, $notification->payload);
}
