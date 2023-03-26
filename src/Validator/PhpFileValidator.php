<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Validator;

use PhpCodeMinifier\Exceptions\IncorrectFileException;

class PhpFileValidator
{
    /** @throws IncorrectFileException */
    public function validate(string $filePath): void
    {
        $this->assertIsNotDirectory($filePath);
        $this->assertIsFile($filePath);
        $this->assertFileHasPhpExtension($filePath);
    }

    /** @throws IncorrectFileException */
    private function assertIsNotDirectory(string $filePath): void
    {
        if (is_dir($filePath)) {
            throw new IncorrectFileException(sprintf('File (%s) is a directory.', $filePath));
        }
    }

    /** @throws IncorrectFileException */
    private function assertIsFile(string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new IncorrectFileException(sprintf('File (%s) not found.', $filePath));
        }
    }

    /** @throws IncorrectFileException */
    private function assertFileHasPhpExtension(string $filePath): void
    {
        if (!str_ends_with($filePath, '.php')) {
            throw new IncorrectFileException(sprintf('File (%s) must have .php extension.', $filePath));
        }
    }
}
