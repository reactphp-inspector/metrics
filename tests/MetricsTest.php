<?php declare(strict_types=1);

namespace ReactInspector\Tests;

use React\EventLoop\Factory;
use ReactInspector\Collector\MetricCollector;
use ReactInspector\Metric;
use ReactInspector\Metrics;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use function WyriHaximus\React\timedPromise;

/**
 * @internal
 */
final class MetricsTest extends AsyncTestCase
{
    public function testBasic(): void
    {
        $loop = Factory::create();

        $metricsCollection = [];
        $loop->futureTick(function () use ($loop, &$metricsCollection): void {
            $metrics = new Metrics($loop, 1, new MetricCollector());
            $metrics->subscribe(function ($metric) use (&$metricsCollection): void {
                $metricsCollection[] = $metric;
            });
        });

        $begin = \microtime(true);
        $this->await(timedPromise($loop, 5), $loop, 10.0);
        $end = \microtime(true);

        self::assertCount(4, $metricsCollection);
        /** @var Metric $metric */
        foreach ($metricsCollection as $index => $metric) {
            self::assertSame('inspector', $metric->name());
            self::assertTrue(
                $begin < $metric->time() &&
                $end > $metric->time()
            );
            foreach ($metric->measurements() as $measurement) {
                foreach ($measurement->tags() as $tag) {
                    self::assertSame('measurement', $tag->key());
                    self::assertTrue(\in_array($tag->value(), ['metrics', 'uptime'], true));
                }
            }
        }
    }
}
