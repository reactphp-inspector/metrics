<?php declare(strict_types=1);

namespace ReactInspector\Collector;

use function ApiClients\Tools\Rx\observableFromArray;
use React\EventLoop\LoopInterface;
use ReactInspector\CollectorInterface;
use ReactInspector\GlobalState;
use ReactInspector\Metric;
use Rx\ObservableInterface;

final class MetricCollector implements CollectorInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function collect(): ObservableInterface
    {
        return observableFromArray([
            new Metric(
                'inspector.metrics',
                (float)\count(GlobalState::get())
            ),
        ]);
    }

    public function cancel(): void
    {
        unset($this->loop);
        $this->loop = null;
    }
}
