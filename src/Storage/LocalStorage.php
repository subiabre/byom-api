<?php

namespace App\Storage;

class LocalStorage implements StorageInterface
{
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
}
