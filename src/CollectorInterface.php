<?php declare(strict_types=1);

namespace ReactInspector;

use Rx\Observable;

interface CollectorInterface
{
    /**
     * Request a stream of metrics.
     */
    public function collect(): Observable;

    /**
     * Cancel all outstanding operations.
     */
    public function cancel(): void;
}
