<?php
declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO\Vanilla\Sql;

final class DumbChunk implements ChunkInterface
{
    /**
     * @var string
     */
    private string $txt;

    public function __construct(string $txt)
    {
        $this->txt = $txt;
    }

    public function processed(): string
    {
        return $this->txt;
    }
}
