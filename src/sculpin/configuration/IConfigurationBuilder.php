<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace sculpin\configuration;

interface IConfigurationBuilder {

    /**
     * Build a configuration
     * @return array
     */
    public function build();

}