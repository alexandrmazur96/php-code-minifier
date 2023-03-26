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
        $filePath = __DIR__ . '/../Fixtures/ActualFiles/non-php-file.txt';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage(sprintf('File (%s) must have .php extension.', $filePath));

        $this->minifier->minifyFile($filePath);
    }

    public function testMinifyFileIsDirectory(): void
    {
        $filePath = __DIR__ . '/../Fixtures/ActualFiles';
        $this->expectException(IncorrectFileException::class);
        $this->expectExceptionMessage(sprintf('File (%s) is a directory.', $filePath));

        $this->minifier->minifyFile($filePath);
    }

    public function testMinifyFileNotFound(): void
    {
        $filePath = __DIR__ . '/../Fixtures/ActualFiles/file_not_exists.php';
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
        $expectedResult = file_get_contents(__DIR__ . '/../Fixtures/Expected/' . basename($filePath));
        $this->assertEquals($expectedResult, $actualResult);
    }

    /** @dataProvider phpFilesProvider */
    public function testMinifyContent(string $filePath): void
    {
        $actualResult = $this->minifier->minifyString(file_get_contents($filePath));
        $expectedResult = file_get_contents(__DIR__ . '/../Fixtures/Expected/' . basename($filePath));
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @psalm-return Generator<string, list{string}, mixed, void>
     */
    public static function phpFilesProvider(): Generator
    {
        yield 'php-file-without-code' => [__DIR__ . '/../Fixtures/ActualFiles/no-php-code.php'];
        yield 'php-file-with-just-php-code' => [__DIR__ . '/../Fixtures/ActualFiles/just-php-code.php'];
        yield 'php-file-with-php-class' => [__DIR__ . '/../Fixtures/ActualFiles/PhpClass.php'];
        yield 'php-file-with-mixed-php-and-html' => [__DIR__ . '/../Fixtures/ActualFiles/mixed-php-and-html.php'];
        yield 'php-file-with-single-line-comment' => [
            __DIR__ . '/../Fixtures/ActualFiles/php-code-with-single-comment.php',
        ];
    }
}
