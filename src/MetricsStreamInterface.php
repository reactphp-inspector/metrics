<?php

declare(strict_types=1);

namespace ReactInspector;

use Rx\ObservableInterface;

interface MetricsStreamInterface extends ObservableInterface
{
    /**
     * Any implementers of this interface should extend Subject and only call onNext with instances of Metric.
     */
}
