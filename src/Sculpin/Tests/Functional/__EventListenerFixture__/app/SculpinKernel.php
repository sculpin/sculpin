<?php

class SculpinKernel extends \Sculpin\Bundle\SculpinBundle\HttpKernel\AbstractKernel
{
    protected function getAdditionalSculpinBundles(): array
    {
        return [
            \Sculpin\Tests\Functional\EventListenerTestFixtureBundle\EventListenerTestFixtureBundle::class,
        ];
    }
}
