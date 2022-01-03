<?php

declare(strict_types=1);

namespace Neu\Console\ErrorHandler;

use Exception as RootException;
use Neu\Console\Command;
use Neu\Console\Input;
use Neu\Console\Output;
use Neu\Console\Style;
use Neu\Console\Terminal;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function is_string;

final class StandardErrorHandler implements ErrorHandlerInterface
{
    public function __construct(private readonly Terminal $terminal)
    {
    }

    /**
     * Handle the given error and return the proper exit code.
     */
    public function handle(Input\InputInterface $input, Output\OutputInterface $output, RootException $exception, ?Command\Command $command = null): int
    {
        if ($output instanceof Output\ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $io = new Style\Style($this->terminal, $input, $output);
        $this->renderMessage($io, $exception);
        $this->renderSource($io, $exception);
        $this->renderTrace($io, $exception);

        $code = $exception->getCode();
        if (is_string($code)) {
            $code = Str\to_int($code) ?? Command\ExitCode::Failure->value;
        }

        if ($code > Command\ExitCode::ExitStatusOutOfRange->value) {
            $code %= (Command\ExitCode::ExitStatusOutOfRange->value + 1);
        }

        return $code;
    }

    private function renderMessage(Style\StyleInterface $io, RootException $exception,): void
    {
        $io->block($exception->getMessage(), Output\Verbosity::Normal, $exception::class, 'fg=white; bg=red;', ' | ', true, true, false);
    }

    private function renderSource(Style\StyleInterface $io, RootException $exception,): void
    {
        $io->writeLine(
            Str\format(
                '- %s:%d%s',
                $exception->getFile(),
                $exception->getLine(),
                Output\OutputInterface::END_OF_LINE,
            ),
            Output\Verbosity::Verbose,
        );
    }

    private function renderTrace(Style\StyleInterface $io, RootException $exception,): void
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
            $io->writeLine(
                '<fg=yellow>Exception trace: </>' . Output\OutputInterface::END_OF_LINE,
                Output\Verbosity::VeryVerbose,
            );

            foreach ($frames as $frame) {
                if (Iter\contains_key($frame, 'class')) {
                    $call = Str\format(' %s%s%s()', $frame['class'], $frame['type'], $frame['function']);
                } else {
                    $call = Str\format(' %s()', $frame['function']);
                }

                $io->writeLine($call, Output\Verbosity::VeryVerbose);
                $io->write(Str\format(
                    ' ↪ <fg=green>%s</>',
                    $frame['file'] . (Iter\contains_key($frame, 'line') ? (':' . $frame['line']) : ''),
                ), Output\Verbosity::VeryVerbose);
                $io->nl(2, Output\Verbosity::VeryVerbose);
            }
        }
    }
}
