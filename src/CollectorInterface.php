<?php declare(strict_types=1);

namespace ReactInspector;

use Rx\Observable;

interface CollectorInterface
{
    /**
     * Request a array of metrics.
     *
     * @return Observable<Metric[]>
     */
    public function collect(): Observable;

    /**
     * Cancel all outstanding operations.
     */
    public function cancel(): void;
}
