<?php

declare(strict_types=1);

namespace ItalyStrap\Tests\Stubs;

use ItalyStrap\Config\AccessValueInArrayWithNotationTrait;

class AccessValueInArrayWithNotation
{
    use AccessValueInArrayWithNotationTrait {
        findValue as public;
        insertValue as public;
        deleteValue as public;
    }
}
