<?php
/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 06/06/17
 * Time: 15:04
 */

namespace TrombiBundle\EventListener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use TrombiBundle\Entity\Person;
use TrombiBundle\Service\FileUploader;

class PersonUploadListener
{
    private $uploader;

    public function __construct(FileUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->uploadFile($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();

        $this->uploadFile($entity);
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Person) {
            return;
        }
        $entity->setUpdatedAt();
         if ($fileName = $entity->getPicture()) {
            $entity->setPicFile(new File($this->uploader->getTargetDir().'/'.$fileName));
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Person) {
            return;
        }

        if(is_file($entity->getPicture())) {
            unlink($entity->getPicture());
        }
    }


    private function uploadFile($entity)
    {
        // upload only works for Product entities
        if (!$entity instanceof Person) {
            return;
        }

        $file = $entity->getPicFile();
        // only upload new files
        if (!$file instanceof UploadedFile) {
            return;
        }

        $fileName = $this->uploader->upload($file);
        $entity->setPicture($fileName);
    }
}