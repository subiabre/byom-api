<?php

namespace App\Service\Metadata;

class MetadataBag
{
    private array $metadata;

    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Get the value at the specified keys
     * @param string $keys
     * @return null|mixed The value contained at the specified keys
     */
    private function getData(string... $keys)
    {
        $data = $this->metadata;

        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                return null;
            }

            $data = $data[$key];
        }

        return $data;
    }

    /**
     * Obtain the unfiltered array of metadata
     * @return array
     */
    public function getAll(): array
    {
        return $this->metadata;
    }

    /**
     * Obtain an associative array of common properties, sanitized
     * @return array
     */
    public function getCommon(): array
    {
        return [
            'title' => $this->getTitle(),
            'album' => $this->getAlbum(),
            'artist' => $this->getArtist(),
        ];
    }

    /**
     * Obtain an associative array of extra properties, sanitized
     * @return array
     */
    public function getExtra(): array
    {
        return [
            'totaltracks' => $this->getTotalTracks(),
            'tracknumber' => $this->getTrackNumber(),
            'playtime' => $this->getPlaytime()
        ];
    }

    public function getPicture(): ?array
    {
        $picture = $this->getData('comments', 'picture', 0);
        return !$picture ? null : [
            'data' => base64_encode($picture['data']),
            'image_mime' => $picture['image_mime']
        ];
    }

    public function getTotalTracks(): string
    {
        $totaltracks = $this->getData('tags', 'id3v2', 'totaltracks', 0);
        
        if (!$totaltracks) $totaltracks = $this->getData('tags', 'id3v1', 'totaltracks', 0);
        if (!$totaltracks) $totaltracks = $this->getData('tags', 'asf', 'totaltracks', 0);
        if (!$totaltracks) $totaltracks = $this->getData('asf', 'comments', 'totaltracks', 0);
        if (!$totaltracks) $totaltracks = $this->getData('tags', 'vorbiscomment', 'totaltracks', 0);

        return mb_convert_encoding($totaltracks, 'UTF-8');
    }

    public function getTrackNumber(): string
    {
        $tracknumber = $this->getData('tags', 'id3v2', 'track_number', 0);
        
        if (!$tracknumber) $tracknumber = $this->getData('tags', 'id3v1', 'track', 0);
        if (!$tracknumber) $tracknumber = $this->getData('tags', 'asf', 'track', 0);
        if (!$tracknumber) $tracknumber = $this->getData('asf', 'comments', 'track', 0);
        if (!$tracknumber) $tracknumber = $this->getData('tags', 'vorbiscomment', 'tracknumber', 0);
        if (!$tracknumber) $tracknumber = $this->getData('comments', 'tracknumber', 0);

        return mb_convert_encoding($tracknumber, 'UTF-8');
    }

    public function getPlaytime(): string
    {
        return $this->getData('playtime_string');
    }

    public function getTitle(): string
    {
        $title = $this->getData('tags', 'id3v2', 'title', 0);

        if (!$title) $title = $this->getData('tags', 'id3v1', 'title', 0);
        if (!$title) $title = $this->getData('tags', 'asf', 'title', 0);
        if (!$title) $title = $this->getData('asf', 'comments', 'title', 0);
        if (!$title) $title = $this->getData('tags', 'vorbiscomment', 'title', 0);
        if (!$title) $title = $this->getData('comments', 'album', 0);
        
        return mb_convert_encoding($title, 'UTF-8');
    }

    public function getAlbum(): string
    {
        $album = $this->getData('tags', 'id3v2', 'album', 0);

        if (!$album) $album = $this->getData('tags', 'id3v1', 'album', 0);
        if (!$album) $album = $this->getData('tags', 'asf', 'album', 0);
        if (!$album) $album = $this->getData('asf', 'comments', 'album', 0);
        if (!$album) $album = $this->getData('tags', 'vorbiscomment', 'album', 0);
        if (!$album) $album = $this->getData('comments', 'album', 0);

        return mb_convert_encoding($album, 'UTF-8');
    }

    public function getArtist(): string
    {
        $artist = $this->getData('tags', 'id3v2', 'artist', 0);

        if (!$artist) $artist = $this->getData('tags', 'id3v1', 'artist', 0);
        if (!$artist) $artist = $this->getData('tags', 'asf', 'artist', 0);
        if (!$artist) $artist = $this->getData('asf', 'comments', 'artist', 0);
        if (!$artist) $artist = $this->getData('tags', 'vorbiscomment', 'artist', 0);
        if (!$artist) $artist = $this->getData('commments', 'artist', 0);

        return mb_convert_encoding($artist, 'UTF-8');
    }
}
