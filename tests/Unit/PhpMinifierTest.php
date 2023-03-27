<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Tests\Unit;

use Generator;
use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\PhpMinifier;
use PhpCodeMinifier\PhpTokenizer;
use PhpCodeMinifier\Tests\TestCase;
use PhpCodeMinifier\Validator\PhpFileValidator;

final class PhpMinifierTest extends TestCase
{
    private PhpMinifier $minifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->minifier = new PhpMinifier(
            new PhpFileValidator(),
            new PhpTokenizer()
        );
    }

    protected function tearDown(): void
    {
        unset($this->minifier);
        parent::tearDown();
    }

    public function testMinifyFileNotPhpFile(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles/non-php-file.txt';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage(sprintf('File (%s) must have .php extension.', $filePath));

        $this->minifier->minifyFile($filePath);
    }

    public function testMinifyFileIsDirectory(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage(sprintf('File (%s) is a directory.', $filePath));

        $this->minifier->minifyFile($filePath);
    }

    public function testMinifyFileNotFound(): void
    {
        $filePath = self::FIXTURES_DIR . '/ActualFiles/file_not_exists.php';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage(sprintf('File (%s) not found.', $filePath));

        $this->minifier->minifyFile($filePath);
    }

    /**
     * @dataProvider phpFilesProvider
     * @throws IncorrectFileException
     */
    public function testMinifyFile(string $filePath): void
    {
        $actualResult = $this->minifier->minifyFile($filePath);
        $expectedResult = file_get_contents(self::FIXTURES_DIR . '/Expected/' . basename($filePath));
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertMinifiedCodeLintedOk($actualResult);
    }

    /** @dataProvider phpFilesProvider */
    public function testMinifyContent(string $filePath): void
    {
        $actualResult = $this->minifier->minifyString(file_get_contents($filePath));
        $expectedResult = file_get_contents(self::FIXTURES_DIR . '/Expected/' . basename($filePath));
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertMinifiedCodeLintedOk($actualResult);
    }

    /**
     * @dataProvider phpFilesProvider
     * @throws IncorrectFileException
     */
    public function testMinifyFileToFile(string $filePath): void
    {
        $outputFile = $filePath . '.min.php';
        try {
            $this->minifier->minifyFileToFile($filePath, $outputFile);
            $this->assertFileExists($outputFile);
            $this->assertMinifiedFileLintedOk($outputFile);
            $this->assertFileEquals(self::FIXTURES_DIR . '/Expected/' . basename($filePath), $outputFile);
        } finally {
            @unlink($outputFile);
        }
    }

    public function testMinifyFileToFileOutputFileIsDirectory(): void
    {
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('File is a directory: ' . self::FIXTURES_DIR . '/ActualFiles');
        $this->minifier->minifyFileToFile(
            self::FIXTURES_DIR . '/ActualFiles/just-php-code.php',
            self::FIXTURES_DIR . '/ActualFiles'
        );
    }

    public function testMinifyFileToFileOutputFileIsNotWriteable(): void
    {
        $readonlyFilePath = $this->createReadonlyFile();
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('Unable to write to file: ' . $readonlyFilePath);

        try {
            $this->minifier->minifyFileToFile(
                self::FIXTURES_DIR . '/ActualFiles/just-php-code.php',
                $readonlyFilePath
            );
        } finally {
            @unlink($readonlyFilePath);
        }
    }

    /**
     * @dataProvider phpFilesProvider
     * @throws IncorrectFileException
     */
    public function testMinifyStringToFile(string $filePath): void
    {
        $outputFile = $filePath . '.min.php';
        try {
            $this->minifier->minifyStringToFile(file_get_contents($filePath), $outputFile);
            $this->assertFileExists($outputFile);
            $this->assertMinifiedFileLintedOk($outputFile);
            $this->assertFileEquals(self::FIXTURES_DIR . '/Expected/' . basename($filePath), $outputFile);
        } finally {
            @unlink($outputFile);
        }
    }

    public function testMinifyStringToFileOutputFileIsDirectory(): void
    {
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('File is a directory: ' . self::FIXTURES_DIR . '/ActualFiles');
        $this->minifier->minifyStringToFile(
            file_get_contents(self::FIXTURES_DIR . '/ActualFiles/just-php-code.php'),
            self::FIXTURES_DIR . '/ActualFiles'
        );
    }

    public function testMinifyStringToFileOutputFileIsNotWriteable(): void
    {
        $readonlyFilePath = $this->createReadonlyFile();
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage('Unable to write to file: ' . $readonlyFilePath);

        try {
            $this->minifier->minifyStringToFile(
                file_get_contents(self::FIXTURES_DIR . '/ActualFiles/just-php-code.php'),
                $readonlyFilePath
            );
        } finally {
            @unlink($readonlyFilePath);
        }
    }

    /** @psalm-return Generator<string, list{string}, mixed, void> */
    public static function phpFilesProvider(): Generator
    {
        yield 'without-code' => [self::FIXTURES_DIR . '/ActualFiles/no-php-code.php'];
        yield 'with-just-php-code' => [self::FIXTURES_DIR . '/ActualFiles/just-php-code.php'];
        yield 'with-php-class' => [self::FIXTURES_DIR . '/ActualFiles/PhpClass.php'];
        yield 'with-mixed-php-and-html' => [self::FIXTURES_DIR . '/ActualFiles/mixed-php-and-html.php'];
        yield 'with-single-line-comment' => [self::FIXTURES_DIR . '/ActualFiles/php-code-with-single-comment.php'];
    }

    /**
     * We do understand, that calling shell_exec is unsafe, but for testing purposes we leave it as is.
     * @psalm-suppress ForbiddenCode
     */
    private function assertMinifiedCodeLintedOk(string $minifiedCode): void
    {
        $filePath = self::FIXTURES_DIR . '/tmp.php';
        file_put_contents($filePath, $minifiedCode);
        $lintResult = shell_exec('php -l ' . $filePath);
        try {
            $this->assertStringContainsString('No syntax errors detected in', $lintResult ?? '');
        } finally {
            @unlink($filePath);
        }
    }

    /**
     * We do understand, that calling shell_exec is unsafe, but for testing purposes we leave it as is.
     * @psalm-suppress ForbiddenCode
     */
    private function assertMinifiedFileLintedOk(string $filePath): void
    {
        $lintResult = shell_exec('php -l ' . $filePath);
        $this->assertStringContainsString('No syntax errors detected in', $lintResult ?? '');
    }

    private function createReadonlyFile(): string
    {
        $filename = uniqid('test_', true) . '.php';
        $mask = umask(0377); // disables --wxrwxrwx permissions
        $fh = fopen($filename, "wb");
        umask($mask);
        fwrite($fh, '');
        fclose($fh);

        return $filename;
    }
}
