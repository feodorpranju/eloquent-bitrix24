<?php

namespace Pranju\Bitrix24\Traits;

use JetBrains\PhpStorm\NoReturn;

trait Dumps
{
    /**
     * Dumps and dies
     *
     * @param mixed ...$vars
     * @return void
     */
    #[NoReturn]
    public function dd(...$vars): void
    {
        dd($this->toArray(), ...$vars);
    }

    /**
     * Dumps
     *
     * @param mixed ...$vars
     * @return static
     */
    public function dump(...$vars): static
    {
        dump($this->toArray(), ...$vars);

        return $this;
    }
}