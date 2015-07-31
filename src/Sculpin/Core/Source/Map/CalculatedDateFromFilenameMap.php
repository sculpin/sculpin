<?php

/*
 * This file is a part of Sculpin.
 *
 * (c) Dragonfly Development Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class CalculatedDateFromFilenameMap implements MapInterface
{
    public function process(SourceInterface $source)
    {
        if ($source->data()->get('calculated_date')) {
            return;
        }

        if (!preg_match(
            '/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(\d+|)/',
            $source->relativePathname(),
            $matches
        )
        ) {
            return;
        }

        list ($dummy, $year, $month, $day, $time) = $matches;
        $parts = array(implode('-', array($year, $month, $day)));
        if ($time && false !== strtotime($time)) {
            $parts[] = $time;
        }

        $calculatedDate = strtotime(implode(' ', $parts));
        $source->data()->set('calculated_date', $calculatedDate);
        if (!$source->data()->get('date')) {
            $source->data()->set('date', $calculatedDate);
        }
    }
}
