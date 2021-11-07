<?php

declare(strict_types=1);

namespace ReactInspector;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Rx\DisposableInterface;
use Rx\Observable;
use Rx\ObserverInterface;
use Rx\Subject\Subject;

use function ApiClients\Tools\Rx\observableFromArray;

final class Metrics extends Subject implements MetricsStreamInterface
{
    private LoopInterface $loop;

    private float $interval;

    private ?TimerInterface $timer = null;

    /** @var array<int, CollectorInterface> */
    private array $collectors = [];

    public function __construct(LoopInterface $loop, float $interval, CollectorInterface ...$collectors)
    {
        $this->loop       = $loop;
        $this->interval   = $interval;
        $this->collectors = $collectors;
    }

    public function removeObserver(ObserverInterface $observer): bool
    {
        $return = parent::removeObserver($observer);
        if (! $this->hasObservers()) {
            if ($this->timer !== null) {
                $this->loop->cancelTimer($this->timer);
                $this->timer = null;
            }

            foreach ($this->collectors as $instance) {
                $instance->cancel();
            }

            $this->collectors = [];
        }

        return $return;
    }

    // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    protected function _subscribe(ObserverInterface $observer): DisposableInterface
    {
        if ($this->timer === null) {
            $collect     = function (): void {
                observableFromArray($this->collectors)->flatMap(static function (CollectorInterface $collector): Observable {
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
