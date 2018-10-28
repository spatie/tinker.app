<?php

namespace Tests\Feature;

use React\EventLoop\LoopInterface;
use Tests\TestCase;

class EventLoopTest extends TestCase
{
    /** @test */
    public function it_returns_the_same_event_loop_instance()
    {
        $loop1 = $this->app->make(LoopInterface::class);

        $loop2 = $this->app->make(LoopInterface::class);

        $this->assertEquals($loop1, $loop2);
    }
}
