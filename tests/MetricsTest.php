<?php

declare(strict_types=1);

namespace ReactInspector\Tests;

use React\EventLoop\Loop;
use ReactInspector\Collector\MetricCollector;
use ReactInspector\CollectorInterface;
use ReactInspector\Metric;
use ReactInspector\Metrics;
use Rx\Observable;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function ApiClients\Tools\Rx\observableFromArray;
use function assert;
use function in_array;
use function microtime;
use function WyriHaximus\React\timedPromise;

/**
 * @internal
 */
final class MetricsTest extends AsyncTestCase
{
    public function testBasic(): void
    {
        $loop = Loop::get();

        $disposable        = null;
        $metricsCollection = [];
        $collector         = new class implements CollectorInterface {
            private bool $collectCalled = false;
            private bool $cancelCalled  = false;

            public function collect(): Observable
            {
                $this->collectCalled = true;

                return observableFromArray([]);
            }

            public function cancel(): void
            {
                $this->cancelCalled = true;
            }

            public function allCalled(): bool
            {
                return $this->collectCalled && $this->cancelCalled;
            }
        };
        $loop->futureTick(static function () use ($loop, &$metricsCollection, &$disposable, $collector): void {
            $metrics    = new Metrics($loop, 1, new MetricCollector(), $collector);
            $disposable = $metrics->subscribe(static function ($metric) use (&$metricsCollection): void {
                $metricsCollection[] = $metric;
            });
        });

        $begin = microtime(true);
        $this->await(timedPromise(5), 10.0);
        $end = microtime(true);

        self::assertCount(4, $metricsCollection);
        foreach ($metricsCollection as $index => $metric) {
            assert($metric instanceof Metric);
            self::assertSame('reactphp_inspector', $metric->config()->name());
            self::assertTrue(
                $begin < $metric->time() &&
                $end > $metric->time()
            );
            foreach ($metric->measurements()->get() as $measurement) {
                foreach ($measurement->tags()->get() as $tag) {
                    self::assertSame('measurement', $tag->key());
                    self::assertTrue(in_array($tag->value(), ['metrics', 'uptime'], true));
                }
            }
        }

        self::assertNotNull($disposable);
        $disposable->dispose();
        self::assertTrue($collector->allCalled());
    }
}
