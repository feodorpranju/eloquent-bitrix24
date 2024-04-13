<?php

namespace Pranju\Bitrix24\Eloquent;

use Pranju\Bitrix24\Contracts\Command;

class Builder extends \Illuminate\Database\Eloquent\Builder
{
    /**
     * Retrieves select command
     *
     * @return Command
     */
    public function toCmd(): Command
    {
        return $this->query->toCmd();
    }
}