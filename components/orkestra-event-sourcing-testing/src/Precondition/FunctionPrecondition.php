<?php

namespace Morebec\Orkestra\EventSourcing\Testing\Precondition;

/**
 * Precondition executing a given function.
 */
class FunctionPrecondition implements TestStagePreconditionInterface
{
    /** @var callable */
    private $func;

    private array $params;

    private function __construct(callable $func, $params = [])
    {
        $this->func = $func;
        $this->params = $params;
    }

    public static function as(callable $func, $params = []): self
    {
        return new self($func, $params);
    }

    /**
     * Specifies the parameters to pass to the function.
     *
     * @param array $params
     *
     * @return $this
     */
    public function withParams(...$params): self
    {
        $this->params = $params;

        return $this;
    }

    public function run(): void
    {
        ($this->func)(...$this->params);
    }
}
