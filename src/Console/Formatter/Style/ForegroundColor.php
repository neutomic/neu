<?php declare(strict_types=1);

namespace Neu\Console\Formatter\Style;

enum ForegroundColor: string {
  case Black = '30';
  case Red = '31';
  case Green = '32';
  case Yellow = '33';
  case Blue = '34';
  case Magenta = '35';
  case Cyan = '36';
  case White = '37';
  case Default = '39';
}
