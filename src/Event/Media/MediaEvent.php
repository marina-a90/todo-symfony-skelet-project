<?php

namespace App\Event\Media;

use Symfony\Component\EventDispatcher\Event;

class MediaEvent extends Event
{
    private $data;

    const PHOTO = "photo.posted";

    public function __construct(array $data) {
        $this->data = $data;
    }


    public function getData() {
        return $this->data;
    }

    public function setData(array $data) {
        return $this->data = $data;
    }

}
