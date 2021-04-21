<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

/**
 * The Typed event projector is a utility implementation of a {@link ProjectorInterface} that simply
 * extends {@link AbstractDenormalizingProjector} and uses the {@link EventHandlerMethodResolvingProjectorTrait},
 * to provide a simple API for defining projectors.
 */
abstract class AbstractTypedEventProjector extends AbstractDenormalizingProjector
{
    use EventHandlerMethodResolvingProjectorTrait;
}
