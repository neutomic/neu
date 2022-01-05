<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Output;

final class NoteBlock extends Block
{
    public function __construct(Output\OutputInterface $output)
    {
        parent::__construct($output, 'NOTE', 'comment', ' ! ');
    }
}
