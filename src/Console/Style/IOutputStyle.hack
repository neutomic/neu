<?php declare(strict_types=1);

namespace Neu\Console\Style;

use Nuxed\Console\Output;

interface IOutputStyle extends IStyle, Output\IOutput {
  /**
   * Return the underlying output object.
   */
  public function getOutput(): Output\IOutput;
}
