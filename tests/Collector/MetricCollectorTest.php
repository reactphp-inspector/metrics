<?php

declare(strict_types=1);

namespace ReactInspector\Tests\Collector;

use ReactInspector\Collector\MetricCollector;
use ReactInspector\Measurement;
use ReactInspector\Metric;
use ReactInspector\Tag;
use Rx\React\Promise;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

use function array_map;
use function assert;
use function current;
use function round;
use function Safe\sort;

/**
 * @internal
 */
final class MetricCollectorTest extends AsyncTestCase
{
    public function testBasics(): void
    {
        $collector = new MetricCollector();

        /** @var Metric[] $metrics */
        $metrics = $this->await(Promise::fromObservable($collector->collect()->toArray()));

        self::assertCount(1, $metrics);

        $metric = current($metrics);
        assert($metric instanceof Metric);
        self::assertCount(1, $metric->tags()->get());
        self::assertCount(2, $metric->measurements()->get());

//        self::assertSame(
//            [
//                ['reactphp_inspector_internal' => 'true'],
//            ],
//            array_map(static function (Tag $tag): array {
//                return [
//                    $tag->key() => $tag->value(),
//                ];
//            }, $metric->tags()->get())
//        );
        self::assertSame(
            [
                [
                    'measurement' => ['measurement' => 'metrics'],
                ],
                [
                    'measurement' => ['measurement' => 'uptime'],
                ],
            ],
            array_map(static function (Measurement $measurement): array {
                return array_map(static function (Tag $tag): array {
                    return [
                        $tag->key() => $tag->value(),
                    ];
                }, $measurement->tags()->get());
            }, $metric->measurements()->get())
        );

        $values = array_map(static function (Measurement $measurement): float {
            return round($measurement->value(), 1);
        }, $metric->measurements()->get());
        sort($values);

        self::assertSame(
            [
                0.0,
                0.0,
            ],
            $values
        );

        $collector->cancel();
    }
}
