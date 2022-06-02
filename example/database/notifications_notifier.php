<?php

use Psl\{IO, Str};

$database = require __DIR__ . '/bootstrap.php';

$channel = 'test';
$notifier = $database->getNotifier($channel);

IO\write_line('notifying "test" channel.');

$input = IO\input_handle();
while (true) {
    IO\write('> ');

    $message = $input->read();
    $message = Str\strip_suffix($message, "\n");

    $notifier->notify($message);
}
