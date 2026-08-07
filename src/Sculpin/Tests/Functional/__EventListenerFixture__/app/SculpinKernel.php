<?php

use Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel;
use Sculpin\Tests\Functional\EventListenerTestFixtureBundle\EventListenerTestFixtureBundle;

class SculpinKernel extends AbstractKernel
{
    protected function getAdditionalSculpinBundles(): array
    {
        return [
            EventListenerTestFixtureBundle::class,
        ];
    }
}
