<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait DeprecatedTrait
{

    public function push($index, $value): Config
    {
        return $this->add($index, $value);
    }
}
