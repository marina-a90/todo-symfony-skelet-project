<?php

namespace App\EventListener\Serializer;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\User\User;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\VisitorInterface;
use Sonata\MediaBundle\Provider\ImageProvider;

/**
 * Class MediaSerializerListener
 *
 */
class MediaSerializerSubscriber implements EventSubscriberInterface
{
    /**
     * @var ImageProvider
     */
    private $imageProvider;

    /**
     * MediaSerializerSubscriber constructor.
     *
     * @param ImageProvider $imageProvider
     */
    public function __construct(ImageProvider $imageProvider)
    {
        $this->imageProvider = $imageProvider;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => 'serializer.post_serialize',
                'class'  => 'App\Entity\User\User',
                'method' => 'serializeUserPhoto'
            ]
        ];
    }

    /**
     * Do sonata media image serialization
     *
     * @param VisitorInterface $visitor
     * @param Media|null $media
     * @param string $fieldName
     * @param array $formats
     */
    private function serialize(
        VisitorInterface $visitor,
        Media $media = null,
        $fieldName,
        array $formats
    ) {
        $urls = array();
        if ($media) {
            $url = null;

            foreach ($formats as $format) {
                $url = $this->imageProvider->generatePublicUrl(
                    $media,
                    $this->imageProvider->getFormatName($media, $format)
                );

                if ($url) {
                    $urls[$format] = $url;
                    $url = null;
                }
            }

            $urls["fileName"] = $media->getName();
        }

        $visitor->setData($fieldName, $urls);
    }

    /**
     * Serialize user photo
     *
     * @param ObjectEvent $event
     */
    public function serializeUserPhoto(ObjectEvent $event)
    {
        /** @var User $user */
        $user = $event->getObject();

        /** @var VisitorInterface $visitor */
        $visitor = $event->getVisitor();

        if ($event->getVisitor()->hasData('photo')) {
            $visitor->setData('photo', null);
            $this->serialize(
                $visitor,
                $user->getPhoto(),
                'photo',
                array('small', 'big')
            );
        }
    }

}
