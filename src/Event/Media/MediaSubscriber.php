<?php

namespace App\Event\Media;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Entity\User\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\VisitorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sonata\MediaBundle\Provider\ImageProvider;

class MediaSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var ImageProvider
     */
    private $imageProvider;

    public function __construct(EntityManagerInterface $manager, ImageProvider $imageProvider)
    {
        $this->manager = $manager;
        $this->imageProvider = $imageProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            MediaEvent::PHOTO => 'onPhotoPublished',
        ];
    }

    public function onPhotoPublished(MediaEvent $event)
    {
        /** @var User $user */
        $user = $event->getData()['data'];
        $userPhoto = $user->getPhoto();

        /** @var VisitorInterface $visitor */
        $visitor = $event->getVisitor();

        if ( !empty($userPhoto) ) {

            $this->serialize(
                $visitor,
                $userPhoto,
                'photo',
                array('small', 'big')
            );

            $user->setPhoto($userPhoto);

            $this->manager->persist($user);
            $this->manager->flush();

        }

        $event->stopPropagation();
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

        $visitor->addData($fieldName, $urls);
    }

}
