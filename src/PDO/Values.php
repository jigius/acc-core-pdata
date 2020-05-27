<?php
/**
 * This file is part of the jigius/acc-core-pdata library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2020 Jigius <jigius@gmail.com>
 * @link https://github.com/jigius/acc-core-pdata GitHub
 */

declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO;

use PDOStatement;

/**
 * Class Values
 * @package Acc\Core\PersistentData\PDO
 */
final class Values implements ValuesInterface
{
    /**
     * @var array
     */
    private array $itm;

    /**
     * Values constructor.
     */
    public function __construct()
    {
        $this->itm = [];
    }

    /**
     * @param ValueInterface $value
     * @return ValuesInterface
     */
    public function with(ValueInterface $value): ValuesInterface
    {
        $obj = $this->blueprinted();
        $obj->itm[] = $value;
        return $obj;
    }

    /**
     * @param PDOStatement $stmt
     */
    public function bind(PDOStatement $stmt): void
    {
        foreach ($this->itm as $itm) {
            $itm->bind($stmt);
        }
    }

    /**
     * Clones instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self();
        $obj->itm = $this->itm;
        return $obj;
    }
}
