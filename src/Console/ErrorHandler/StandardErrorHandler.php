<?php

declare(strict_types=1);

namespace Neu\Console\ErrorHandler;

use Exception as RootException;
use Neu\Console\Block\Block;
use Neu\Console\Command;
use Neu\Console\CommandProvider;
use Neu\Console\Input;
use Neu\Console\Output;
use Psl\Filesystem;
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
        $source_highlighted = $this->renderSource($output, $exception);
        $this->renderTrace($output, $exception, $source_highlighted);

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

    private function renderSource(Output\OutputInterface $output, RootException $exception): bool
    {
        if (!$this->isNeu($exception->getFile())) {
            $output->writeLine(Str\format('- <fg=bright-green;underline;bold>%s:%d</>', $exception->getFile(), $exception->getLine()), Output\Verbosity::Verbose);
            $output->writeLine('', Output\Verbosity::Verbose);

            return true;
        }

        $output->writeLine(Str\format('- %s:%d', $exception->getFile(), $exception->getLine()), Output\Verbosity::Verbose);
        $output->writeLine('', Output\Verbosity::Verbose);

        return false;
    }

    private function renderTrace(Output\OutputInterface $output, RootException $exception, bool $source_highlighted): void
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
                // render user exception and neu exception sources in different colors.
                // as the error is usually coming from the user, not neu.
                $file = $frame['file'];
                if ($source_highlighted || $this->isNeu($file)) {
                    $trace_format = ' ↪ <fg=gray>%s</>';
                } else {
                    $trace_format = ' ↪ <fg=bright-green;underline;bold>%s</>';
                    $source_highlighted = true;
                }

                if (Iter\contains_key($frame, 'class')) {
                    $output->writeLine(Str\format('%s%s%s()', $frame['class'], $frame['type'], $frame['function']), Output\Verbosity::VeryVerbose);
                } else {
                    $output->writeLine(Str\format(' %s()', $frame['function']), Output\Verbosity::VeryVerbose);
                }

                $output->writeLine(Str\format($trace_format, $file . (Iter\contains_key($frame, 'line') ? (':' . $frame['line']) : '')), Output\Verbosity::VeryVerbose);
                $output->writeLine('', Output\Verbosity::VeryVerbose);
            }
        }
    }

    /**
     * Determine if the given file is part of Neu.
     */
    private function isNeu(string $file): bool
    {
        return Str\starts_with($file, Filesystem\get_directory(__DIR__));
    }
}
