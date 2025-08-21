<?php

declare(strict_types=1);

arch('does not use debug functions')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump', 'sleep', 'exit'])
    ->not->toBeUsed();
