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
        $this->assertCodeLintedOk($actualResult);
    }

    /** @dataProvider phpFilesProvider */
    public function testMinifyContent(string $filePath): void
    {
        $actualResult = $this->minifier->minifyString(file_get_contents($filePath));
        $expectedResult = file_get_contents(__DIR__ . '/../Fixtures/Expected/' . basename($filePath));
        $this->assertEquals($expectedResult, $actualResult);
        $this->assertCodeLintedOk($actualResult);
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
    private function assertCodeLintedOk(string $minifiedCode): void
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
}
