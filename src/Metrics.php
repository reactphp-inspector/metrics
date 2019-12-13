<?php declare(strict_types=1);

namespace ReactInspector;

use function ApiClients\Tools\Rx\observableFromArray;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Rx\DisposableInterface;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

final class Metrics extends Subject implements MetricsStreamInterface
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var float
     */
    private $interval;

    /**
     * @var TimerInterface|null
     */
    private $timer;

    /**
     * @var CollectorInterface[]
     */
    private $collectors = [];

    /**
     * @param LoopInterface        $loop
     * @param float                $interval
     * @param CollectorInterface[] $collectors
     */
    public function __construct(LoopInterface $loop, float $interval, CollectorInterface ...$collectors)
    {
        $this->loop = $loop;
        $this->interval = $interval;
        $this->collectors = $collectors;
    }

    public function removeObserver(ObserverInterface $observer): bool
    {
        $return = parent::removeObserver($observer);
        if (!$this->hasObservers()) {
            $this->loop->cancelTimer($this->timer);
            $this->timer = null;
            foreach ($this->collectors as $index => $instance) {
                $instance->cancel();
            }

            $this->collectors = [];
        }

        return $return;
    }

    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        if ($this->timer === null) {
            $collect = function (): void {
                observableFromArray($this->collectors)->flatMap(function (CollectorInterface $collector) {
                    return $collector->collect();
                })->subscribe(function (Metric $metric): void {
                    $this->onNext($metric);
                });
            };
            $this->timer = $this->loop->addPeriodicTimer($this->interval, $collect);
            $collect();
        }

        return parent::_subscribe($observer);
    }
}
