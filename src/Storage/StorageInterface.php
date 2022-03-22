<?php

namespace App\Storage;

interface StorageInterface
{
    /**
     * Set the path to this file, changing further reading of this Storage
     * @param string $path The path to this file
     */
    public function setPath(string $path): self;

    /**
     * Obtain the path to this file
     */
    public function getPath(): string;

    /**
     * Obtains the hash string based on the file
     */
    public function getHash(): string;
}
