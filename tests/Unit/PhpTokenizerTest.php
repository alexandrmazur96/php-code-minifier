<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Tests\Unit;

use Closure;
use Generator;
use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\PhpTokenizer;
use PhpCodeMinifier\Tests\TestCase;

/** @psalm-import-type _PhpToken from PhpTokenizer */
final class PhpTokenizerTest extends TestCase
{
    private PhpTokenizer $phpTokenizer;

    private static Closure $tokenFilter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->phpTokenizer = new PhpTokenizer();
    }

    protected function tearDown(): void
    {
        unset($this->phpTokenizer);
        parent::tearDown();
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$tokenFilter =
            /** @psalm-param _PhpToken|string $token */
            static fn(string|array $token): bool => !(trim(is_array($token) ? $token[1] : $token) === '');
    }

    /**
     * @dataProvider filesToTokenizeProvider
     * @throws IncorrectFileException
     */
    public function testTokenizeFile(string $filePath): void
    {
        $expectedCount = count(
            array_filter(token_get_all(file_get_contents($filePath)), self::$tokenFilter)
        );
        $tokens = $this->phpTokenizer->tokenizeFile($filePath);
        $actualTokenCount = array_sum(array_map('count', $tokens));
        $this->assertEquals($expectedCount, $actualTokenCount);
    }

    /** @dataProvider filesToTokenizeProvider */
    public function testTokenizeString(string $filePath): void
    {
        $fileContent = file_get_contents($filePath);
        $expectedCount = count(
            array_filter(token_get_all($fileContent), self::$tokenFilter)
        );
        $tokens = $this->phpTokenizer->tokenizeString($fileContent);
        $actualTokenCount = array_sum(array_map('count', $tokens));
        $this->assertEquals($expectedCount, $actualTokenCount);
    }

    /**
     * @psalm-return Generator<string, list{string}, mixed, void>
     */
    public static function filesToTokenizeProvider(): Generator
    {
        yield 'non-php-file' => [__DIR__ . '/../Fixtures/ActualFiles/non-php-file.txt'];
        yield 'php-file-without-code' => [__DIR__ . '/../Fixtures/ActualFiles/no-php-code.php'];
        yield 'php-file-with-just-php-code' => [__DIR__ . '/../Fixtures/ActualFiles/just-php-code.php'];
        yield 'php-file-with-php-class' => [__DIR__ . '/../Fixtures/ActualFiles/PhpClass.php'];
        yield 'php-file-with-mixed-php-and-html' => [__DIR__ . '/../Fixtures/ActualFiles/mixed-php-and-html.php'];
        yield 'php-file-with-single-line-comment' => [
            __DIR__ . '/../Fixtures/ActualFiles/php-code-with-single-comment.php',
        ];
    }
}
