<?php

declare(strict_types=1);

namespace Neu\Console\Block;

use Neu\Console\Output;

trait BlockTrait
{
    /**
     * Create a new success block.
     */
    public function success(Output\OutputInterface $output): SuccessBlock
    {
        return new SuccessBlock($output);
    }

    /**
     * Create a new section block.
     */
    public function section(Output\OutputInterface $output): SectionBlock
    {
        return new SectionBlock($output);
    }

    /**
     * Create a new title block.
     */
    public function title(Output\OutputInterface $output): TitleBlock
    {
        return new TitleBlock($output);
    }

    /**
     * Create a new text block.
     */
    public function text(Output\OutputInterface $output): TextBlock
    {
        return new TextBlock($output);
    }

    /**
     * Create a new note block.
     */
    public function note(Output\OutputInterface $output): NoteBlock
    {
        return new NoteBlock($output);
    }

    /**
     * Create a new warning block.
     */
    public function warning(Output\OutputInterface $output): WarningBlock
    {
        return new WarningBlock($output);
    }

    /**
     * Create a new caution block.
     */
    public function caution(Output\OutputInterface $output): CautionBlock
    {
        return new CautionBlock($output);
    }

    /**
     * Create a new error block.
     */
    public function error(Output\OutputInterface $output): ErrorBlock
    {
        return new ErrorBlock($output);
    }
}
