<?php

namespace Morebec\Orkestra\EventSourcing\Projection;

use Morebec\Orkestra\EventSourcing\EventStore\RecordedEventDescriptor;
use PHPUnit\Framework\TestCase;

class ProjectorGroupTest extends TestCase
{
    public function testProject(): void
    {
        $projector = $this->getMockBuilder(ProjectorInterface::class)->getMock();
        $group = new ProjectorGroup('default', [$projector]);

        $projector->expects($this->once())->method('project');
        $group->project($this->getMockBuilder(RecordedEventDescriptor::class)->disableOriginalConstructor()->getMock());
    }

    public function testBoot(): void
    {
        $projector = $this->getMockBuilder(ProjectorInterface::class)->getMock();
        $group = new ProjectorGroup('default', [$projector]);

        $projector->expects($this->once())->method('boot');
        $group->boot();
    }

    public function testReset(): void
    {
        $projector = $this->getMockBuilder(ProjectorInterface::class)->getMock();
        $group = new ProjectorGroup('default', [$projector]);

        $projector->expects($this->once())->method('reset');
        $group->reset();
    }

    public function testGetName(): void
    {
        $group = new ProjectorGroup('default', []);
        self::assertEquals('default', $group->getName());
    }

    public function testShutdown(): void
    {
        $projector = $this->getMockBuilder(ProjectorInterface::class)->getMock();
        $group = new ProjectorGroup('default', [$projector]);

        $projector->expects($this->once())->method('shutdown');
        $group->shutdown();
    }
}
