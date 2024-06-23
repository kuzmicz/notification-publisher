<?php

namespace App\NotificationPublisher\Application\Middleware;

use App\NotificationPublisher\Application\Exception\ThrottledException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;

readonly class ThrottlingMiddleware implements MiddlewareInterface
{
    public function __construct(private RateLimiterFactory $limiterFactory)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof ThrottleInterface) {
            $limiter = $this->limiterFactory->create(get_class($message).'user_' . $message->getUserId());
            if (false === $limiter->consume()->isAccepted()) {
                throw new ThrottledException('Rate limit exceeded for '.get_class($message));
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
