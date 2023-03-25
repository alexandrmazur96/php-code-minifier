<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Validator;

use PhpCodeMinifier\Exceptions\IncorrectFileException;

class PhpFileValidator
{
    /** @throws IncorrectFileException */
    public function validate(string $filePath): void
    {
        if (!$this->isFileHasPhpExtension($filePath)) {
            throw new IncorrectFileException('File must have .php extension');
        }
    }

    private function isFileHasPhpExtension(string $filePath): bool
    {
        return str_ends_with($filePath, '.php');
    }
}
