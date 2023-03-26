<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Tests\Unit\Validator;

use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\Tests\TestCase;
use PhpCodeMinifier\Validator\PhpFileValidator;

final class PhpFileValidatorTest extends TestCase
{
    private PhpFileValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new PhpFileValidator();
    }

    protected function tearDown(): void
    {
        unset($this->validator);
        parent::tearDown();
    }

    /** @throws IncorrectFileException */
    public function testValidateFileHasPhpExtension(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validator->validate(self::FIXTURES_DIR . '/ActualFiles/PhpClass.php');
    }

    public function testValidatorFileIsDirectory(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('File (' . $filePath . ') is a directory.');
        $this->validator->validate($filePath);
    }

    public function testValidatorFileNotExists(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles/file_not_exists.php';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('File (' . $filePath . ') not found.');
        $this->validator->validate($filePath);
    }

    public function testValidateFileHasNotPhpExtension(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles/non-php-file.txt';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('File (' . $filePath . ') must have .php extension.');
        $this->validator->validate($filePath);
    }
}
