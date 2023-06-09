<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\Validator\PhpFileValidator;

class PhpMinifier
{
    public const VERSION = '1.1.0';
    public const RELEASE_DATE = '2023-03-27';

    /**
     * We need these symbols as keys because searching that symbols in array wouldn't be so fast.
     * @var array<string, bool>
     */
    public const SPEC_SYMBOLS = [
        ';'   => true,
        '('   => true,
        ')'   => true,
        '{'   => true,
        '}'   => true,
        '='   => true,
        '=='  => true,
        '!='  => true,
        '===' => true,
        '!==' => true,
        '++'  => true,
        '--'  => true,
        '=>'  => true,
        '>'   => true,
        '<'   => true,
        '>='  => true,
        '<='  => true,
        '<=>' => true,
        '.'   => true,
        ','   => true,
        '['   => true,
        ']'   => true,
        '!'   => true,
        '&'   => true,
        '&&'  => true,
        '|'   => true,
        '||'  => true,
        '^'   => true,
        '~'   => true,
        '*'   => true,
        '/'   => true,
        '+'   => true,
        '-'   => true,
        '%'   => true,
        ':'   => true,
        '?'   => true,
        '@'   => true,
        '"'   => true,
        '`'   => true,
        '<?=' => true,
        '?>'  => true,
    ];

    public function __construct(
        private PhpFileValidator $phpFileValidator,
        private PhpTokenizer     $phpTokenizer
    ) {
    }

    /**
     * Minify given php file.
     *
     * @throws IncorrectFileException
     */
    public function minifyFile(string $filePath): string
    {
        $this->phpFileValidator->validate($filePath);

        $tokens = $this->phpTokenizer->tokenizeFile($filePath);

        return $this->minifyTokens($tokens);
    }

    /**
     * Minify given php file and write result to another file.
     * @throws IncorrectFileException
     */
    public function minifyFileToFile(string $filePath, string $outputFilePath): void
    {
        $minifiedContent = $this->minifyFile($filePath);

        $this->writeToFile($minifiedContent, $outputFilePath);
    }

    /**
     * Minify given php script content and write result to another file.
     * @throws IncorrectFileException
     */
    public function minifyStringToFile(string $phpScriptContent, string $outputFilePath): void
    {
        $minifiedContent = $this->minifyString($phpScriptContent);

        $this->writeToFile($minifiedContent, $outputFilePath);
    }

    /**
     * Minify given php script content.
     */
    public function minifyString(string $phpScriptContent): string
    {
        $tokens = $this->phpTokenizer->tokenizeString($phpScriptContent);

        return $this->minifyTokens($tokens);
    }

    /**
     * @throws IncorrectFileException
     */
    private function writeToFile(string $content, string $outputFilePath): void
    {
        $this->assertOutputFilePathIsNotDirectory($outputFilePath);
        $this->assertOutputFilePathIsWriteable($outputFilePath);

        $fh = fopen($outputFilePath, 'wb');
        if ($fh === false) {
            throw new IncorrectFileException('Unable to write to file: ' . $outputFilePath);
        }

        if (!fwrite($fh, $content)) {
            fclose($fh);
            throw new IncorrectFileException('Unable to write to file: ' . $outputFilePath);
        }
        fclose($fh);
    }

    /** @param array<string,array<array-key,array{token:string}>> $tokens */
    private function minifyTokens(array $tokens): string
    {
        $str = '';
        foreach ($tokens as $tokensType => $tokenItems) {
            if (str_contains($tokensType, 'php')) {
                $str .= $this->handlePhpTokens($tokenItems);
            } else {
                foreach ($tokenItems as $tokenItem) {
                    $str .= $tokenItem['token'];
                }
            }
        }

        return $str;
    }

    /** @param array<int, array{token:string}> $tokens */
    private function handlePhpTokens(array $tokens): string
    {
        $str = '';
        foreach ($tokens as $i => $token) {
            if (array_key_exists($i + 1, $tokens)) {
                if (isset(self::SPEC_SYMBOLS[$tokens[$i + 1]['token']])) {
                    // if next token is spec symbol - no need to add space before it.
                    $str .= $token['token'];
                    continue;
                }

                if ($token['token'] === 'else' && $tokens[$i + 1]['token'] === 'if') {
                    // "else if" construction could be written as elseif
                    $str .= $token['token'];
                    continue;
                }

                if (str_ends_with($token['token'], '*/')) {
                    // if current token is end of comment - no need to add space after it.
                    $str .= $token['token'];
                    continue;
                }
            }

            if (array_key_exists($token['token'], self::SPEC_SYMBOLS)) {
                // if current token is spec symbol - no need to add space after it.
                $str .= $token['token'];
            } elseif ($token['token'] !== '') {
                // each other statements should be divided by space.
                $str .= $token['token'] . ' ';
            }
        }

        return $str;
    }

    /** @throws IncorrectFileException */
    public function assertOutputFilePathIsNotDirectory(string $outputFilePath): void
    {
        if (is_dir($outputFilePath)) {
            throw new IncorrectFileException('File is a directory: ' . $outputFilePath);
        }
    }

    /** @throws IncorrectFileException */
    public function assertOutputFilePathIsWriteable(string $outputFilePath): void
    {
        if (file_exists($outputFilePath) && !is_writable($outputFilePath)) {
            throw new IncorrectFileException('Unable to write to file: ' . $outputFilePath);
        }
    }
}
