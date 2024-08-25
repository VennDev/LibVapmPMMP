<?php

/**
 * Vapm - A library support for PHP about Async, Promise, Coroutine, Thread, GreenThread
 *          and other non-blocking methods. The library also includes some Javascript packages
 *          such as Express. The method is based on Fibers & Generator & Processes, requires
 *          you to have php version from >= 8.1
 *
 * Copyright (C) 2023  VennDev
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

declare(strict_types=1);

namespace vennv\vapm;

use Generator;
use RuntimeException;
use function array_shift;

interface ChannelInterface
{

    /**
     * @param mixed $message
     * @return Generator
     *
     * This function is used to send a message to the channel.
     */
    public function send($message): Generator;

    /**
     * @return Generator
     *
     * This function is used to receive a message from the channel.
     */
    public function receiveGen(): Generator;

    /**
     * @return mixed
     *
     * This function is used to receive a message from the channel.
     */
    public function receive(): mixed;

    /**
     * @return bool
     *
     * This function is used to check if the channel is empty.
     */
    public function isEmpty(): bool;

    /**
     * @return void
     *
     * This function is used to close the channel.
     */
    public function close(): void;

    /**
     * @return bool
     *
     * This function is used to check if the channel is closed.
     */
    public function isClosed(): bool;

}

final class Channel implements ChannelInterface
{

    /**
     * @var mixed[]
     */
    private array $queue = [];

    private bool $locked = false;

    private bool $closed = false;

    public function send($message): Generator
    {
        $this->exceptionIfClosed();
        while ($this->locked) yield;
        $this->locked = true;
        $this->queue[] = $message;
        $this->locked = false;
    }

    public function receiveGen(): Generator
    {
        $this->exceptionIfClosed();
        while ($this->locked) {
            CoroutineGen::run();
            yield;
        }
        $this->locked = true;
        $message = array_shift($this->queue);
        $this->locked = false;
        return yield $message;
    }

    public function receive(): mixed
    {
        $this->exceptionIfClosed();
        while ($this->locked) {
            CoroutineGen::run();
        }
        $this->locked = true;
        $message = array_shift($this->queue);
        $this->locked = false;
        return $message;
    }

    public function isEmpty(): bool
    {
        return empty($this->queue);
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    private function exceptionIfClosed(): void
    {
        if ($this->closed) throw new RuntimeException('Channel is closed');
    }

}
