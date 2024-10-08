<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Validator\PhpFileValidator;

class MinifierFactory
{
    public static function create(): PhpMinifier
    {
        return new PhpMinifier(
            new PhpFileValidator(),
            new PhpTokenizer()
        );
    }
}
