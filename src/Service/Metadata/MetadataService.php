<?php

namespace App\Service\Metadata;

use getID3;

class MetadataService
{
    private getID3 $extractor;

    public function __construct()
    {
        $this->extractor = new getID3;
    }

    public function getMetadata($file): ?MetadataBag
    {
        $result = $this->extractor->analyze($file);

        if (array_key_exists('error', $result)) return null;
        if (array_key_exists('mime_type', $result) && explode("/", $result['mime_type'])[0] !== "audio") return null;

        return new MetadataBag($result);
    }
}
