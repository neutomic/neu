<?php

declare(strict_types=1);

namespace Neu\Console\Feedback;

use Neu\Console\Output\OutputInterface;

trait FeedbackTrait
{
    /**
     * Construct and return a new instance of `ProgressBarFeedback`.
     */
    public function progress(OutputInterface $output, int $total, string $message = '', int $interval = 100): ProgressBarFeedback
    {
        $progress = new ProgressBarFeedback($output, $total, $message, $interval);

        $progress->setCharacterSequence(['▓', '', '░']);

        return $progress;
    }

    /**
     * Construct and return a new `WaitFeedback` object.
     *
     * @param int $total The total number of cycles of the process
     * @param string $message The message presented with the feedback
     * @param int $interval The time interval the feedback should update
     */
    public function wait(OutputInterface $output, int $total, string $message = '', int $interval = 100): WaitFeedback
    {
        return new WaitFeedback($output, $total, $message, $interval);
    }
}
