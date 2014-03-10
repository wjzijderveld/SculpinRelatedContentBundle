<?php
/**
 * Created at 04/02/14 21:40
 */

namespace Wjzijderveld\Sculpin\RelatedContentBundle\EventListener;


use Sculpin\Core\Event\FormatEvent;
use Wjzijderveld\Sculpin\RelatedContentBundle\Manager;

class PageData
{
    /**
     * @var \Wjzijderveld\Sculpin\RelatedContentBundle\Manager
     */
    private $relatedContentManager;

    public function __construct(Manager $relatedContentManager)
    {
        $this->relatedContentManager = $relatedContentManager;
    }

    public function onSculpinCoreBeforeformat(FormatEvent $event)
    {
        $pageData = $event->formatContext()->data()->get('page');

        $relatedContent = array();
        if (isset($pageData['related'])) {
            $relatedContent = $this->relatedContentManager->getRelatedContent($pageData['related']);
        }

        $event->formatContext()->data()->set('related_content', $relatedContent);
    }
} 
