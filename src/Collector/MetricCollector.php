<?php

declare(strict_types=1);

namespace ReactInspector\Collector;

use ReactInspector\CollectorInterface;
use ReactInspector\Config;
use ReactInspector\Measurement;
use ReactInspector\Measurements;
use ReactInspector\Metric;
use ReactInspector\Tag;
use ReactInspector\Tags;
use Rx\Observable;

use function ApiClients\Tools\Rx\observableFromArray;
use function hrtime;

final class MetricCollector implements CollectorInterface
{
    private float $startTime;

    public function __construct()
    {
        $this->startTime = hrtime(true) * 1e-9;
    }

    public function collect(): Observable
    {
        return observableFromArray([
            Metric::create(
                new Config(
                    'reactphp_inspector',
                    'counter',
                    ''
                ),
                new Tags(
                    new Tag('reactphp_inspector_internal', 'true'),
                ),
                new Measurements(
                    new Measurement(0.0, new Tags(new Tag('measurement', 'metrics'))),
                    new Measurement((hrtime(true) * 1e-9) - $this->startTime, new Tags(new Tag('measurement', 'uptime'))),
                )
            ),
        ]);
    }

    public function cancel(): void
    {
    }
}
