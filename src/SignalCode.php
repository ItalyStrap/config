<?php

declare(strict_types=1);

namespace ItalyStrap\Config;

final class SignalCode
{
    public const NONE = null;

    public const STOP_TRAVERSAL = 1;

    public const REMOVE_NODE = 2;

    public const CONTINUE = 3;

    public const SKIP_CHILDREN = 4;
}
