<?php

declare(strict_types=1);

use Psl\IO;
use Psl\Async;

$database = require __DIR__ . '/bootstrap.php';

$channel = 'test';

$notifier = $database->getNotifier($channel);
$listener = $database->getListener($channel);

Async\run(static function() use($notifier, $listener): void {
    Async\sleep(1);
    $notifier->notify('hello');
    Async\sleep(0.5);
    $notifier->notify(' ');
    Async\sleep(0.5);
    $notifier->notify('world!');
    Async\sleep(1);
    $listener->close();
});

foreach ($listener->listen() as $notification) {
    IO\write($notification->payload);
}

IO\write_line('');
