<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Console\Command;

use Sculpin\Bundle\SculpinBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class ContainerAwareCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @return ContainerInterface
     *
     * @throws \RuntimeException    if the expected Sculpin Console Application instance could not be found
     */
    protected function getContainer(): ContainerInterface
    {
        if (null === $this->container) {
            $app = $this->getApplication();

            if (!$app instanceof Application) {
                throw new \RuntimeException('Sculpin Application not found!');
            }

            $this->container = $app->getKernel()->getContainer();
        }

        return $this->container;
    }

    /**
     * Set Container.
     *
     * @param ContainerInterface $container Container
     *
     * @see ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
