<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\PictureController;
use App\Filter\OrderRandomFilter;
use App\Repository\MusicRepository;
use App\Storage\StorageInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MusicRepository::class)]
#[ORM\Index(name: 'hash_idx', columns: ['hash'])]
#[ApiResource(
    collectionOperations: ['get'],
    itemOperations: [
        'get',
        'picture' => [
            'method' => 'GET',
            'path' => '/music/{id}/picture',
            'controller' => PictureController::class
        ]
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'title' => 'partial',
        'album' => 'partial',
        'artist' => 'partial'
    ]
)]
#[ApiFilter(OrderRandomFilter::class, properties: ['id'])]
class Music
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'object')]
    #[ApiProperty(writable: false)]
    private $storage;

    #[ORM\Column(type: 'string', length: 255)]
    #[ApiProperty(writable: false)]
    private $hash;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $album;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $artist;

    #[ORM\Column(type: 'array', nullable: true)]
    #[ApiProperty(readable: false, writable: false)]
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

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

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
