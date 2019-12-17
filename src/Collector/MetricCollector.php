<?php declare(strict_types=1);

namespace ReactInspector\Collector;

use function ApiClients\Tools\Rx\observableFromArray;
use ReactInspector\CollectorInterface;
use ReactInspector\Measurement;
use ReactInspector\Metric;
use ReactInspector\Tag;
use Rx\ObservableInterface;

final class MetricCollector implements CollectorInterface
{
    /**
     * @var float
     */
    private $startTime;

    public function __construct()
    {
        $this->startTime = \hrtime(true) * 1e-9;
    }

    public function collect(): ObservableInterface
    {
        return observableFromArray([
            new Metric(
                'reactphp_inspector',
                [
                    new Tag('reactphp_inspector_internal', 'true'),
                ],
                [
                    new Measurement(0.0, new Tag('measurement', 'metrics')),
                    new Measurement((\hrtime(true) * 1e-9) - $this->startTime, new Tag('measurement', 'uptime')),
                ]
            ),
        ]);
    }

    public function cancel(): void
    {
    }
}
