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
        yield 'non-php-file' => [self::FIXTURES_DIR . '/ActualFiles/non-php-file.txt'];
        yield 'without-code' => [self::FIXTURES_DIR . '/ActualFiles/no-php-code.php'];
        yield 'with-just-php-code' => [self::FIXTURES_DIR . '/ActualFiles/just-php-code.php'];
        yield 'with-php-class' => [self::FIXTURES_DIR . '/ActualFiles/PhpClass.php'];
        yield 'with-mixed-php-and-html' => [self::FIXTURES_DIR . '/ActualFiles/mixed-php-and-html.php'];
        yield 'with-single-line-comment' => [self::FIXTURES_DIR . '/ActualFiles/php-code-with-single-comment.php'];
    }
}
