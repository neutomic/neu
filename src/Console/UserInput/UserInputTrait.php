<?php

declare(strict_types=1);

namespace Neu\Console\UserInput;

use Neu\Console\Input\InputInterface;
use Neu\Console\Output\OutputInterface;

trait UserInputTrait
{
    /**
     * Construct and return a new `Confirm` object given the default answer.
     */
    public function confirm(InputInterface $input, OutputInterface $output, string $default = ''): Confirm
    {
        $confirm = new Confirm($input, $output);
        $confirm->setDefault($default);
        $confirm->setStrict(true);

        return $confirm;
    }

    /**
     * Construct and return a new `Menu` object given the choices and display
     * message.
     *
     * @param array<string, string> $choices
     */
    public function menu(InputInterface $input, OutputInterface $output, array $choices): Menu
    {
        $menu = new Menu($input, $output);
        $menu->setAcceptedValues($choices);
        $menu->setStrict(true);

        return $menu;
    }
}
