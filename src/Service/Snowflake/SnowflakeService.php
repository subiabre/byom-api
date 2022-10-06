<?php

namespace App\Service\Snowflake;

use Godruoyi\Snowflake\Snowflake;

class SnowflakeService
{
    private Snowflake $snowflake;

    public function __construct(
        int $snowflakeEpoch,
    )
    {
        $snowflake = new Snowflake(null, intval(hash('crc32b', $_ENV['HOSTNAME'] ?? ''), 16));
        $snowflake->setStartTimeStamp(intval($snowflakeEpoch) * 1000);
        $snowflake->setSequenceResolver(new CryptSafeRandomSequenceResolver());

        $this->snowflake = $snowflake;
    }

    public function generateId(): string
    {
        return $this->snowflake->id();
    }
}
