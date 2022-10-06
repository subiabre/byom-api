<?php

namespace App\Service\Snowflake;

use Godruoyi\Snowflake\RandomSequenceResolver;

class CryptSafeRandomSequenceResolver extends RandomSequenceResolver
{
    /**
     *  {@inheritdoc}
     */
    public function sequence(int $currentTime)
    {
        if ($this->lastTimeStamp === $currentTime) {
            $this->sequence++;
            $this->lastTimeStamp = $currentTime;

            return $this->sequence;
        }

        $this->sequence = random_int(0, $this->maxSequence);
        $this->lastTimeStamp = $currentTime;

        return 0;
    }
}
