<?php

declare(strict_types=1);

namespace Neu\Console\ErrorHandler;

use Exception as RootException;
use Neu\Console\Block\Block;
use Neu\Console\Command;
use Neu\Console\CommandProvider;
use Neu\Console\Input;
use Neu\Console\Output;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function is_string;

final class StandardErrorHandler implements ErrorHandlerInterface
{
    /**
     * Handle the given error and return the proper exit code.
     */
    public function handle(Input\InputInterface $input, Output\OutputInterface $output, RootException $exception, ?CommandProvider\Reference $command = null): int
    {
        if ($output instanceof Output\ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $this->renderMessage($output, $exception);
        $this->renderSource($output, $exception);
        $this->renderTrace($output, $exception);

        $code = $exception->getCode();
        if (is_string($code)) {
            $code = Str\to_int($code) ?? Command\ExitCode::Failure->value;
        }

        if ($code > Command\ExitCode::ExitStatusOutOfRange->value) {
            $code %= (Command\ExitCode::ExitStatusOutOfRange->value + 1);
        }

        return $code;
    }

    private function renderMessage(Output\OutputInterface $output, RootException $exception): void
    {
        $block = new Block($output, $exception::class, 'fg=white; bg=red;', ' | ', true, true, false);
        $block->display($exception->getMessage());
    }

    private function renderSource(Output\OutputInterface $output, RootException $exception): void
    {
        $output->writeLine(
            Str\format(
                '- %s:%d%s',
                $exception->getFile(),
                $exception->getLine(),
                Output\OutputInterface::END_OF_LINE,
            ),
            Output\Verbosity::Verbose,
        );
    }

    private function renderTrace(Output\OutputInterface $output, RootException $exception): void
    {
        $frames = Vec\filter(
            Vec\map(
                $exception->getTrace(),
                static function (array $frame): array {
                    unset($frame['args']);
                    return $frame;
                },
            ),
            static fn(array $frame): bool => Iter\contains_key($frame, 'function') && Iter\contains_key($frame, 'file'),
        );

        if (0 !== Iter\count($frames)) {
            $output->writeLine(
                '<fg=yellow>Exception trace: </>' . Output\OutputInterface::END_OF_LINE,
                Output\Verbosity::VeryVerbose,
            );

            foreach ($frames as $frame) {
                if (Iter\contains_key($frame, 'class')) {
                    $call = Str\format(' %s%s%s()', $frame['class'], $frame['type'], $frame['function']);
                } else {
                    $call = Str\format(' %s()', $frame['function']);
                }

                $output->writeLine($call, Output\Verbosity::VeryVerbose);
                $output->writeLine(Str\format(
                    ' ↪ <fg=green>%s</>',
                    $frame['file'] . (Iter\contains_key($frame, 'line') ? (':' . $frame['line']) : ''),
                ), Output\Verbosity::VeryVerbose);
                $output->writeLine('', Output\Verbosity::VeryVerbose);
            }
        }
    }
}
