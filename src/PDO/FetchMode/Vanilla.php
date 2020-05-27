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

namespace Acc\Core\PersistentData\PDO\FetchMode;

use Acc\Core\PersistentData\PDO\FetchModeInterface;
use PDOStatement;

/**
 * Class Vanilla
 * Used to sets up various mode types those do not uses any extra parameters
 * @package Acc\Core\PersistentData\PDO\MySql\FetchMode
 */
final class Vanilla implements FetchModeInterface
{
    /**
     * @var int the type of a mode
     */
    private int $mode;

    /**
     * Vanilla constructor.
     * @param int $mode
     */
    public function __construct(int $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @inheritDoc
     */
    public function initialize(PDOStatement $statement): void
    {
        $statement->setFetchMode($this->mode);
    }
}
