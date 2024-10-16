<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\Validator\PhpFileValidator;

class PhpMinifier
{
    public const VERSION = '1.2.2';
    public const RELEASE_DATE = '2024-10-14';

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

        if (fwrite($fh, $content) === false) {
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
        while (['token' => $token] = (array_shift($tokens) ?? ['token' => null])) {
            if ($token === null) {
                break;
            }

            if (str_starts_with($token, '<<<')) {
                if (str_starts_with($token, '<<<\'') || str_starts_with($token, '<<<"')) {
                    // Nowdoc and heredoc with double quote identifier
                    $identifier = substr($token, 4, -1);
                } else {
                    // Heredoc identifier
                    $identifier = substr($token, 3);
                }

                $str .= $token . PHP_EOL;
                while (['token' => $docToken] = (array_shift($tokens) ?? ['token' => null])) {
                    if ($docToken === null) {
                        break;
                    }

                    $str .= $docToken;

                    if ($docToken === $identifier) {
                        // End of heredoc
                        break;
                    }
                }

                continue;
            }

            if (array_key_exists(0, $tokens)) {
                if (isset(self::SPEC_SYMBOLS[$tokens[0]['token']])) {
                    // if next token is spec symbol - no need to add space before it.
                    $str .= $token;
                    continue;
                }

                if ($token === 'else' && $tokens[0]['token'] === 'if') {
                    // "else if" construction could be written as elseif
                    $str .= $token;
                    continue;
                }

                if (str_ends_with($token, '*/')) {
                    // if current token is end of comment - no need to add space after it.
                    $str .= $token;
                    continue;
                }
            }

            if (array_key_exists($token, self::SPEC_SYMBOLS)) {
                // if current token is spec symbol - no need to add space after it.
                $str .= $token;
            } elseif ($token !== '') {
                // each other statements should be divided by space.
                $str .= $token . ' ';
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
