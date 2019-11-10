<?php
declare(strict_types = 1);

namespace FormManager\Groups;

use FormManager\NodeInterface;
use FormManager\InputInterface;
use ArrayAccess;
use IteratorAggregate;
use ArrayIterator;

/**
 * Common utilities for groups of specific inputs (like radio and submits)
 */
abstract class InputGroup implements InputInterface, ArrayAccess, IteratorAggregate
{
    protected $inputs = [];
    private $parentNode;
    private $name = '';

    public function __construct(iterable $inputs = [])
    {
        foreach ($inputs as $value => $input) {
            $this->offsetSet($value, $input);
        }
    }

    public function __clone()
    {
        foreach ($this->inputs as $k => $input) {
            $this->inputs[$k] = (clone $input)->setParentNode($this);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->inputs);
    }

    public function offsetSet($value, $input)
    {
        $input->setAttribute('value', $value);
        $input->setName($this->name);

        $this->inputs[$value] = $input;
    }

    public function offsetGet($value)
    {
        return $this->inputs[$value] ?? null;
    }

    public function offsetUnset($value)
    {
        unset($this->inputs[$value]);
    }

    public function offsetExists($value)
    {
        return isset($this->inputs[$value]);
    }

    public function setValue($value): InputInterface
    {
        foreach ($this->inputs as $input) {
            $input->setValue($value);
        }

        return $this;
    }

    public function getValue()
    {
        foreach ($this->inputs as $input) {
            $value = $input->getValue();

            if ($value !== null) {
                return $value;
            }
        }
    }

    public function isValid(): bool
    {
        foreach ($this->inputs as $input) {
            if (!$input->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function setName(string $name): InputInterface
    {
        $this->name = $name;

        foreach ($this->inputs as $input) {
            $input->setName($name);
        }

        return $this;
    }

    public function getParentNode(): ?NodeInterface
    {
        return $this->parentNode;
    }

    public function setParentNode(NodeInterface $node): NodeInterface
    {
        $this->parentNode = $node;

        return $this;
    }

    public function __toString()
    {
        return implode("\n", $this->inputs);
    }
}
