<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\MusicRepository;
use App\Storage\StorageInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MusicRepository::class)]
#[ApiResource]
class Music
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'object')]
    #[ApiProperty(readable: false)]
    private $storage;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $album;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $artist;

    #[ORM\Column(type: 'array', nullable: true)]
    #[ApiProperty(readable: false)]
    private $picture = [];

    #[ORM\Column(type: 'array', nullable: true)]
    private $metadata = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function setStorage(StorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(?string $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(?string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getPicture(): ?array
    {
        return $this->picture;
    }

    public function setPicture(?array $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
