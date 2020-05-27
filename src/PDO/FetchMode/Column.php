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
use PDOStatement, PDO;

/**
 * Class Column
 * Used to sets up `column` mode type
 * @package Acc\Core\PersistentData\PDO\FetchMode
 */
final class Column implements FetchModeInterface
{
    /**
     * A column's number
     * @var int
     */
    private int $colno;

    /**
     * Column constructor.
     * @param int $colno
     */
    public function __construct(int $colno)
    {
        $this->colno = $colno;
    }

    /**
     * @inheritDoc
     */
    public function initialize(PDOStatement $statement): void
    {
        $statement->setFetchMode(PDO::FETCH_COLUMN, $this->colno);
    }
}
