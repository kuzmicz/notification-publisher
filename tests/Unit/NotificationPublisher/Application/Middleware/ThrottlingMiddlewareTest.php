<?php

namespace App\Tests\Unit\NotificationPublisher\Application\Middleware;

use App\NotificationPublisher\Application\Exception\ThrottledException;
use App\NotificationPublisher\Application\Middleware\ThrottleInterface;
use App\NotificationPublisher\Application\Middleware\ThrottlingMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

class ThrottlingMiddlewareTest extends TestCase
{
    public function testThrottleInterfaceCommand()
    {
        $this->expectException(ThrottledException::class);
        $limiterFactory = new RateLimiterFactory([
            'id' => 'test',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $loggerMock = $this->createMock(LoggerInterface::class);

        $messageThrottle = new class() implements ThrottleInterface {
            public function getUserId() {
                return 1;
            }
        };
        $envelopeThrottle = new Envelope($messageThrottle);

        $middleware = new ThrottlingMiddleware($limiterFactory, $loggerMock);
        $stack = new StackMiddleware();

        $middleware->handle($envelopeThrottle, $stack);
        $middleware->handle($envelopeThrottle, $stack);
    }

    public function testNonThrottleInterfaceCommand()
    {
        $limiterFactory = new RateLimiterFactory([
            'id' => 'test',
            'policy' => 'fixed_window',
            'limit' => 1,
            'interval' => '1 second',
        ], new InMemoryStorage());

        $loggerMock = $this->createMock(LoggerInterface::class);

        $messageNonThrottle = new class() {
        };
        $envelopeNonThrottle = new Envelope($messageNonThrottle);

        $middleware = new ThrottlingMiddleware($limiterFactory, $loggerMock);
        $stack = new StackMiddleware();

        $result = $middleware->handle($envelopeNonThrottle, $stack);
        $result2 = $middleware->handle($envelopeNonThrottle, $stack);

        $this->assertSame($envelopeNonThrottle, $result);
        $this->assertSame($envelopeNonThrottle, $result2);
    }
}
