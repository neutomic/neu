<?php declare(strict_types=1);

namespace Neu\Console\Output;

use Neu\Console\Formatter;
use Psl\Env;
use Psl\IO;
use function function_exists;
use function sapi_windows_vt100_support;
use function stream_isatty;
use const DIRECTORY_SEPARATOR;

final class StreamHandleOutput extends AbstractOutput
{
    private IO\WriteStreamHandleInterface $handle;

    /**
     * Construct a new `Output` object.
     */
    public function __construct(IO\WriteStreamHandleInterface $handle, Verbosity $verbosity = Verbosity::Normal, ?bool $decorated = null, ?Formatter\FormatterInterface $formatter = null)
    {
        $this->handle = $handle;
        if (null === $decorated) {
            $decorated = $this->hasColorSupport();
        }

        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $message, Verbosity $verbosity = Verbosity::Normal, Type $type = Type::Normal): void
    {
        $this->writeTo($this->handle, $message, $verbosity, $type);
    }

    /**
     * Returns true if the stream supports colorization.
     */
    private function hasColorSupport(): bool
    {
        if (Env\get_var('NO_COLOR') !== null) {
            return false;
        }

        $colors = Env\get_var('CLICOLORS');
        if ($colors !== null) {
            if (
                $colors === '1' ||
                $colors === 'yes' ||
                $colors === 'true' ||
                $colors === 'on'
            ) {
                return true;
            }

            if (
                $colors === '0' ||
                $colors === 'no' ||
                $colors === 'false' ||
                $colors === 'off'
            ) {
                return false;
            }
        }

        if (Env\get_var('TRAVIS') !== null) {
            return  true;
        }

        if (Env\get_var('CIRCLECI') !== null) {
            return  true;
        }

        if (Env\get_var('TERM') === 'xterm') {
            return  true;
        }

        if (Env\get_var('TERM_PROGRAM') === 'Hyper') {
            return  true;
        }

        // Follow https://no-color.org/
        if (isset($_SERVER['NO_COLOR']) || false !== getenv('NO_COLOR')) {
            return false;
        }

        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return (function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support($this->handle->getStream()))
                || false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        return stream_isatty($this->handle->getStream());
    }
}
