<?php

namespace Sculpin\Bundle\PostsBundle;

use Sculpin\Contrib\ProxySourceCollection\SourceMassagerInterface;
use Sculpin\Core\Source\SourceInterface;

class PostsSourceMassager implements SourceMassagerInterface
{
    private $defaultPermalink;

    public function __construct($defaultPermalink = null)
    {
        $this->defaultPermalink = $defaultPermalink;
    }

    public function massageSource(SourceInterface $source)
    {
        $this->massageSourceDrafts($source);
        $this->massageSourceDefaultPermalink($source);
        $this->massageSourceCalculatedDate($source);
    }

    private function massageSourceDrafts(SourceInterface $source)
    {
        if ($source->data()->get('draft')) {
            $tags = $source->data()->get('tags');
            if (null === $tags) {
                $tags = array('drafts');
            } else {
                if (!is_array($tags)) {
                    if ($tags) {
                        $tags = array($tags);
                    } else {
                        $tags = array();
                    }
                }

                if (! in_array('drafts', $tags)) {
                    // only add drafts if it isn't already in tags.
                    $tags[] = 'drafts';
                }
            }
            $source->data()->set('tags', $tags);
        }
    }

    private function massageSourceDefaultPermalink(SourceInterface $source)
    {
        if (!$source->data()->get('permalink') and $this->defaultPermalink) {
            $source->data()->set('permalink', $this->defaultPermalink);
        }
    }

    private function massageSourceCalculatedDate(SourceInterface $source)
    {
        if (!$source->data()->get('calculated_date')) {
            if (preg_match('/(\d{4})[\/\-]*(\d{2})[\/\-]*(\d{2})[\/\-]*(\d+?|)/', $source->filename(), $matches)) {
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
