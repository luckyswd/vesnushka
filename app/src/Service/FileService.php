<?php

namespace App\Service;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class FileService
{
    public function __construct(
        private string $targetDirectory,
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $uploadedFile): File
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

        $uploadedFile->move($this->getTargetDirectory(), $fileName);

        $file = new File();
        $file->setFilename($fileName);
        $file->setOriginalFilename($uploadedFile->getClientOriginalName());
        $file->setMimeType($uploadedFile->getMimeType());
        $file->setSize($uploadedFile->getSize());
        $file->setPath($this->getTargetDirectory() . '/' . $fileName);

        $this->em->persist($file);
        $this->em->flush();

        return $file;
    }

    public function remove(File $file): void
    {
        if (file_exists($file->getPath())) {
            unlink($file->getPath());
        }

        $this->em->remove($file);
        $this->em->flush();
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
