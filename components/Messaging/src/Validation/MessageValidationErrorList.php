<?php

namespace Morebec\Orkestra\Messaging\Validation;

use Morebec\Orkestra\Collections\TypedCollection;

/**
 * @extends TypedCollection<MessageValidationError>
 */
class MessageValidationErrorList extends TypedCollection
{
    public function __construct(iterable $errors = [])
    {
        parent::__construct(MessageValidationErrorInterface::class, $errors);
    }

    /**
     * @param MessageValidationErrorInterface $element
     */
    public function add($element): void
    {
        parent::add($element);
    }

    /**
     * @param MessageValidationErrorInterface $element
     */
    public function prepend($element): void
    {
        parent::prepend($element);
    }

    /**
     * @return MessageValidationErrorInterface
     */
    public function getFirst()
    {
        return parent::getFirst();
    }

    /**
     * @return MessageValidationErrorInterface
     */
    public function getLast()
    {
        return parent::getLast();
    }

    /**
     * {@inheritDoc}
     */
    public function get($index)
    {
        return parent::get($index);
    }

    /**
     * Merges a list of errors with the current errors and returns a new collection containing the merge of the two collections.
     *
     * @param MessageValidationErrorList $errors
     *
     * @return MessageValidationErrorList
     */
    public function merge(self $errors): self
    {
        $merged = new self($this->elements);

        foreach ($errors as $error) {
            $merged->add($error);
        }

        return $merged;
    }
}
