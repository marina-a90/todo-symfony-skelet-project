<?php
namespace App\Utils\Media;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiUploadedFile extends UploadedFile
{
    public function __construct($base64Content)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'UploadedFile');
        $file = fopen($filePath, "w");
        stream_filter_append($file, 'convert.base64-decode');
        fwrite($file, $base64Content);
        $meta_data = stream_get_meta_data($file);

        $path = $meta_data['uri'];
        fclose($file);

        $originalName = $this->createOriginalFileName($filePath);

        parent::__construct($path, $originalName, $mimeType = null, $size = null, $error = null, $test = false);
    }

    /**
     * Create original file name in file path
     * Source is base64 and not have original file name
     * @param $filePath
     *
     * @return string
     */
    public function createOriginalFileName($filePath)
    {
        $fileInfo = getimagesize($filePath);

        $extension = "jpg";

        if (in_array($fileInfo['mime'], array('image/jpg', 'image/jpeg'))) {
            $extension = "jpg";
        } elseif (in_array($fileInfo['mime'], array('image/png', 'image/x-png'))) {
            $extension = "png";
        }

        $originalName = md5(uniqid(rand(), true)) . "." . $extension;

        return $originalName;
    }
}
