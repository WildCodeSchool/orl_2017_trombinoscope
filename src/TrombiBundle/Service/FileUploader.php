<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 06/06/17
 * Time: 14:54
 */

namespace TrombiBundle\Service;


use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    public function upload(UploadedFile $file)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }

}