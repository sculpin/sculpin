<?php

namespace Sculpin\Core\Source\Map;

use Sculpin\Core\Source\SourceInterface;

class CalculatedDateFromFilenameMap implements MapInterface
{
    public function process(SourceInterface $source)
    {
        if (!$source->data()->get('calculated_date')) {
            if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(\d+?|)/', $source->relativePathname(), $matches)) {
                list($dummy, $year, $month, $day, $time) = $matches;
                $parts = array(implode('-', array($year, $month, $day)));
                if ($time) {
                    $parts[] = $time;
                }
                $source->data()->set('calculated_date', $calculatedDate = strtotime(implode(' ', $parts)));
                if (!$source->data()->get('date')) {
                    $source->data()->set('date', date('c', $calculatedDate));
                }
            }
        }
    }
}
