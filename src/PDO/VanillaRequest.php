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

namespace Acc\Core\PersistentData\PDO\Request;

use Acc\Core\PersistentData\PDO\ExtendedPDOInterface;
use Acc\Core\PersistentData\PDO\PDOStatementInterface;
use Acc\Core\PersistentData\PDO\Value;
use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\RequestInterface;

/**
 * Class VanillaRequest
 * The example of simple object that implemented contract `RequestInterface`
 * @package Acc\Core\PersistentData\PDO\Request
 */
final class VanillaRequest implements RequestInterface
{
    /**
     * @var string The query string
     */
    private string $q;

    /**
     * @var array The values for the query string
     */
    private array $v;

    /**
     * Executed statement
     * @var PDOStatementInterface|null
     */
    private ?PDOStatementInterface $statement;

    /**
     * VanillaRequest constructor.
     * @param string $query
     * @param array $value
     */
    public function __construct(string $query, array $value = [])
    {
        $this->q = $query;
        $this->v = $value;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
       return
           $printer
                ->with('statement', $this->statement)
                ->finished();
    }

    /**
     * @inheritDoc
     */
    public function executed(ExtendedPDOInterface $pdo): RequestInterface
    {
        if (empty($this->q)) {
            return $this;
        }
        $obj = new self($this->q, $this->v);
        $bp = new Value();
        $stmt = $pdo->prepared($this->q);
        foreach ($this->v as $name => $val) {
            $stmt =
                $stmt
                    ->withValue(
                        $bp
                            ->withName($name)
                            ->withValue($val)
                    );
        }
        $obj->statement = $stmt->executed();
        return $obj;
    }
}
