<?php declare(strict_types=1);

namespace ReactInspector\Tests\Collector;

use React\EventLoop\Factory;
use ReactInspector\Collector\MetricCollector;
use ReactInspector\GlobalState;
use ReactInspector\Metric;
use Rx\React\Promise;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class MetricCollectorTest extends AsyncTestCase
{
    protected function setUp(): void
    {
        GlobalState::clear();
        parent::setUp();
    }

    public function testBasics(): void
    {
        $collector = new MetricCollector(Factory::create());

        /** @var Metric $metric */
        $metric = $this->await(Promise::fromObservable($collector->collect()));
        self::assertSame('inspector.metrics', $metric->getKey());
        self::assertSame(0.0, $metric->getValue());

        GlobalState::incr('key', 32.10);

        /** @var Metric $metric */
        $metric = $this->await(Promise::fromObservable($collector->collect()));
        self::assertSame('inspector.metrics', $metric->getKey());
        self::assertSame(1.0, $metric->getValue());
    }
}
