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
        assert(
            !is_dir($filePath),
            new IncorrectFileException(sprintf('File (%s) is a directory.', $filePath))
        );
    }

    /** @throws IncorrectFileException */
    private function assertIsFile(string $filePath): void
    {
        assert(
            is_file($filePath),
            new IncorrectFileException(sprintf('File (%s) not found.', $filePath))
        );
    }

    /** @throws IncorrectFileException */
    private function assertFileHasPhpExtension(string $filePath): void
    {
        assert(
            str_ends_with($filePath, '.php'),
            new IncorrectFileException(sprintf('File (%s) must have .php extension.', $filePath))
        );
    }
}
