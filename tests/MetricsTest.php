<?php declare(strict_types=1);

namespace ReactInspector\Tests;

use React\EventLoop\Factory;
use ReactInspector\GlobalState;
use ReactInspector\Metric;
use ReactInspector\Metrics;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use function WyriHaximus\React\timedPromise;

/**
 * @internal
 */
final class MetricsTest extends AsyncTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GlobalState::clear();
    }

    public function testBasic(): void
    {
        $loop = Factory::create();

        $metricsCollection = [];
        $loop->futureTick(function () use ($loop, &$metricsCollection): void {
            $metrics = new Metrics($loop, ['ticks'], 1);
            $metrics->subscribe(function ($metric) use (&$metricsCollection): void {
                $metricsCollection[] = $metric;
            });
        });

        $begin = \microtime(true);
        $this->await(timedPromise($loop, 5), $loop, 10);
        $end = \microtime(true);

        self::assertCount(4, $metricsCollection);
        /** @var Metric $metric */
        foreach ($metricsCollection as $index => $metric) {
            self::assertSame('inspector.metrics', $metric->getKey());
            self::assertTrue(
                $begin < $metric->getTime() &&
                $end > $metric->getTime()
            );
        }

        self::assertSame(0.0, $metricsCollection[0]->getValue());
        self::assertSame(1.0, $metricsCollection[1]->getValue());
        self::assertSame(1.0, $metricsCollection[2]->getValue());
        self::assertSame(1.0, $metricsCollection[3]->getValue());
    }
}
