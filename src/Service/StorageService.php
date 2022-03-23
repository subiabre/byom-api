<?php

namespace App\Service;

class StorageService
{
    /**
     * Builds a valid path with the given arguments
     * @return string
     */
    public function buildPath(string... $args): string
    {
        $path = '';
        foreach ($args as $key => $value) {
            $path .= sprintf('%s%s', DIRECTORY_SEPARATOR, ltrim($value, '\\/'));
        }

        return $path;
    }

    /**
     * Recursively scan a directory and load into a one dimension array all the items inside
     * @param string $path
     * @return array
     */
    public function readDirectory(string $path): array
    {
        $directory = scandir($path);
        $files = [];

        foreach ($directory as $key => $value) {
            $item = $this->buildPath(rtrim($path, '\\/'), $value);

            if (!is_dir($item)) {
                $files[] = $item;
            } elseif ($value != "." && $value != "..") {
                $files = array_merge($files, $this->readDirectory($item));
            }
        }

        return $files;
    }
}
