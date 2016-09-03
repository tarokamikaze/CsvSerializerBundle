<?php
namespace Fullspeed\CsvSerializerBundle\Serializer\EventSubscriber;


use Fullspeed\CsvSerializerBundle\Serializer\CsvHeaderFactory;
use Goodby\CSV\Export\Standard\Exporter;

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
            array('event' => 'serializer.pre_serialize', 'method' => 'onPreSerialize'),
        );
    }

    public function onPreSerialize(\JMS\Serializer\EventDispatcher\PreSerializeEvent $event)
    {
        if ($this->isExecuted) {
            return;
        }

        $collectionTypes = array(
            'ArrayCollection',
            'Doctrine\Common\Collections\ArrayCollection',
            'Doctrine\ORM\PersistentCollection',
            'Doctrine\ODM\MongoDB\PersistentCollection',
            'Doctrine\ODM\PHPCR\PersistentCollection'
        );
        // Get the first item.
        $first = $event->getObject();
        if (is_object($data)) {
            foreach ($collectionTypes as $type) {
                if ($data instanceof $type || is_subclass_of($data, $type)) {
                    $first = $data->first();
                    break;
                }
            }
        } elseif (is_array($data)) {
            $first = reset($data);
        }

        $header = $this->headerFactory->generate($first, $event->getContext());

        $this->exporter->export($this->filePath, [$header]);

        $this->isExecuted = true;
    }
}