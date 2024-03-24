<?php

namespace Pranju\Bitrix24\Contracts;

interface Dumpable
{
    /**
     * Sumps and dies
     *
     * @param mixed ...$vars
     * @return void
     */
    public function dd(...$vars): void;

    /**
     * Dumps
     *
     * @param mixed ...$vars
     * @return static
     */
    public function dump(...$vars): static;
}