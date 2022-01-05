<?php

declare(strict_types=1);

namespace Neu\Console\Command;

use Neu\Console\Block;
use Neu\Console\Feedback;
use Neu\Console\Input;
use Neu\Console\Output;
use Neu\Console\UserInput;

abstract class Command implements ApplicationAwareCommandInterface
{
    use ApplicationAwareCommandTrait;
    use Block\BlockTrait;
    use UserInput\UserInputTrait;
    use Feedback\FeedbackTrait;

    /**
     * The `Input` object containing all registered and parsed command line
     * parameters.
     */
    protected Input\InputInterface $input;

    /**
     * The `Output` object to handle output to the user.
     */
    protected Output\OutputInterface $output;

    /**
     * The method that stores the code to be executed when the command is run.
     */
    abstract public function execute(Input\InputInterface $input, Output\OutputInterface $output): int;

    /**
     * @inheritDoc
     */
    final public function run(Input\InputInterface $input, Output\OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return $this->execute($input, $output);
    }
}
