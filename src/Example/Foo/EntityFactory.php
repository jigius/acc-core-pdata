<?php
namespace Acc\Core\PersistentData\Example\Foo;

use Acc\Core\PrinterInterface;
use DomainException, Exception;
use DateTimeImmutable;

/**
 * Class EntityFactory
 * Creates a new `EntityInterface` instance from feeded input data
 * @package Acc\Core\PersistentData\Example\Foo
 */
final class EntityFactory implements PrinterInterface
{
    /**
     * @var array An input data
     */
    private array $i;

    /**
     * @var EntityInterface An instance is used as blueprint of an injected entity
     */
    private EntityInterface $entity;

    /**
     * EntityFactory constructor.
     * @param EntityInterface|null $entity
     */
    public function __construct(EntityInterface $entity = null)
    {
        $this->entity = $entity ?? new Entity();
        $this->i = [];
    }

    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return PrinterInterface
     */
    public function with(string $key, $val): PrinterInterface
    {
        $obj = new self($this->entity);
        $obj->i = $this->i;
        $obj->i[$key] = $val;
        return $obj;
    }

    /**
     * Creates a new `EntityInterface` instance from feeded input data
     * @return EntityInterface
     * @throws Exception
     */
    public function finished(): EntityInterface
    {
        $this->validate();
        $entity =
            $this->entity
                ->withId($this->i['id'])
                ->withMemo($this->i['memo'])
                ->withCreated(
                    DateTimeImmutable::createFromFormat(
                        "Y-m-d H:i:s",
                        $this->i['created']
                    )
                );
        if (!empty($this->i['updated'])) {
            $entity =
                $entity
                    ->withUpdated(
                        DateTimeImmutable::createFromFormat(
                            "Y-m-d H:i:s",
                            $this->i['updated']
                        )
                    );
        }
        return
            $entity
                ->withOption('persisted', true)
                ->withOption('dirty', false);
    }

    /**
     * Validates an input data
     */
    private function validate()
    {
        $keys = [
            "id",
            "memo",
            "created",
        ];
        foreach ($keys as $k) {
            if (!isset($this->i[$k])) {
                throw new DomainException("invalid data");
            }
        }
    }
}
