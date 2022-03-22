<?php

namespace App\Storage;

class LocalStorage implements StorageInterface
{
    public const LOCAL_STORAGE_PATH = '/local';

    private string $path;

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHash(): string
    {
        return hash('sha256', $this->path);
    }
}
