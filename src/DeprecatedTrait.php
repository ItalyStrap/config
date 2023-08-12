<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

trait DeprecatedTrait
{
    /**
     * @deprecated
     */
    #[\Deprecated]
    public function push($index, $value): Config
    {
        return $this->add($index, $value);
    }
}
