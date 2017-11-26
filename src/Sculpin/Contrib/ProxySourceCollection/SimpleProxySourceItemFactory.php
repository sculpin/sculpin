<?php declare(strict_types=1);

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Contrib\ProxySourceCollection;

use ReflectionClass;
use Sculpin\Core\Source\SourceInterface;

class SimpleProxySourceItemFactory implements ProxySourceItemFactoryInterface
{
    private $reflectionClass;

    public function __construct($class = null)
    {
        $this->reflectionClass = new ReflectionClass(
            $class ?: ProxySourceItem::class
        );
    }

    public function createProxySourceItem(SourceInterface $source): ProxySourceItem
    {
        return $this->reflectionClass->newInstance($source);
    }
}
