<?php

/*
 * This file is a part of Sculpin.
 * 
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin;

use sculpin\configuration\Configuration;

class Sculpin {
    
    const VERSION = '@package_version@';
    
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

}
