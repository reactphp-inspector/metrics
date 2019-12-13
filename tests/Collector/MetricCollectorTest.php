<?php declare(strict_types=1);

namespace ReactInspector\Tests\Collector;

use ReactInspector\Collector\MetricCollector;
use ReactInspector\Measurement;
use ReactInspector\Metric;
use ReactInspector\Tag;
use Rx\React\Promise;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;

/**
 * @internal
 */
final class MetricCollectorTest extends AsyncTestCase
{
    public function testBasics(): void
    {
        $collector = new MetricCollector();

        /** @var Metric[] $metric */
        $metrics = $this->await(Promise::fromObservable($collector->collect()->toArray()));

        self::assertCount(1, $metrics);

        /** @var Metric $metric */
        $metric = \current($metrics);
        self::assertCount(1, $metric->tags());
        self::assertCount(2, $metric->measurements());

        $tags = [
            'metric' => \array_map(function (Tag $tag): array {
                return [
                    $tag->key() => $tag->value(),
                ];
            }, $metric->tags()),
            'measurements' => \array_map(function (Measurement $measurement): array {
                return \array_map(function (Tag $tag): array {
                    return [
                        $tag->key() => $tag->value(),
                    ];
                }, $measurement->tags());
            }, $metric->measurements()),
        ];

        self::assertSame(
            [
                'metric' => [
                    [
                        'reactphp_inspector_internal' => 'true',
                    ],
                ],
                'measurements' => [
                    [
                        ['measurement' => 'metrics'],
                    ],
                    [
                        ['measurement' => 'uptime'],
                    ],
                ],
            ],
            $tags
        );

        $values = \array_map(function (Measurement $measurement) {
            return \round($measurement->value(), 1);
        }, $metric->measurements());
        \sort($values);

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
