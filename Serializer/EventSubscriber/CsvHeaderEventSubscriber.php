<?php
namespace Fullspeed\CsvSerializerBundle\Serializer\EventSubscriber;


use Fullspeed\CsvSerializerBundle\Serializer\CsvHeaderFactory;
use Goodby\CSV\Export\Standard\Exporter;
use JMS\Serializer\GraphNavigator;

/**
 * Class CsvHeaderEventSubscriber
 * @package Fullspeed\CsvSerializerBundle\Serializer\EventSubscriber
 */
class CsvHeaderEventSubscriber implements \JMS\Serializer\EventDispatcher\EventSubscriberInterface
{
    /**
     * @var CsvHeaderFactory
     */
    private $headerFactory;

    /**
     * @var Exporter
     */
    private $exporter;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $isExecuted = false;

    /**
     * CsvHeaderEventSubscriber constructor.
     * @param CsvHeaderFactory $headerFactory
     */
    public function __construct(CsvHeaderFactory $headerFactory)
    {
        $this->headerFactory = $headerFactory;
    }

    /**
     * @param Exporter $exporter
     * @param $filePath
     */
    public function initialize(Exporter $exporter, $filePath)
    {
        $this->exporter = $exporter;
        $this->filePath = $filePath;
        $this->isExecuted = false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.pre_serialize',
                'method' => 'onPreSerialize',
                'direction' => 'serialization',
                'format' => 'csv'
            ),
        );
    }

    public function onPreSerialize(\JMS\Serializer\EventDispatcher\PreSerializeEvent $event)
    {
        if ($this->isExecuted) {
            return;
        }

        if($event->getContext()->getFormat() !== 'csv' || $event->getContext()->getDirection() != GraphNavigator::DIRECTION_SERIALIZATION){
            return;
        }

        $collectionTypes = array(
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection'
        );
        foreach ($collectionTypes as $type) {
            if ($event->getObject() instanceof $type || is_subclass_of($event->getObject(), $type)) {
                return;
            }
        }

        $header = $this->headerFactory->generate($event->getObject(), $event->getContext());

        $this->exporter->export($this->filePath, [$header]);

        $this->isExecuted = true;
    }
}