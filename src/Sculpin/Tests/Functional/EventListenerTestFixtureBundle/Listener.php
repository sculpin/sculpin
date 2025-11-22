<?php

declare(strict_types=1);

namespace Sculpin\Tests\Functional\EventListenerTestFixtureBundle;

use Sculpin\Core\Event\SourceSetEvent;
use Sculpin\Core\Sculpin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Listener implements EventSubscriberInterface
{
    public function __construct(protected $outputDir)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Sculpin::EVENT_AFTER_RUN => 'createSuccessFile',
        ];
    }

    public function createSuccessFile(SourceSetEvent $event, string $eventName): void
    {
        file_put_contents($this->outputDir . '/' . $eventName . '.event', $eventName);
    }
}
