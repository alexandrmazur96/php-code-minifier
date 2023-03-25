<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Exceptions\IncorrectFileException;
use PhpCodeMinifier\Validator\PhpFileValidator;

class PhpMinifier
{
    /**
     * We need these symbols as keys because searching that symbols in array wouldn't be so fast.
     * @var array<string, bool>
     */
    public const SPEC_SYMBOLS = [
        ';'  => true,
        '('  => true,
        ')'  => true,
        '{'  => true,
        '}'  => true,
        '='  => true,
        '=>' => true,
        '>=' => true,
        '<=' => true,
        '.'  => true,
        ','  => true,
        '['  => true,
        ']'  => true,
        '!'  => true,
        '&'  => true,
        '|'  => true,
        '^'  => true,
        '~'  => true,
        '*'  => true,
        '/'  => true,
        '+'  => true,
        '-'  => true,
        '%'  => true,
        ':'  => true,
        '?'  => true,
        '@'  => true,
        '"'  => true,
        '`'  => true,
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
     * Minify given php script content.
     */
    public function minifyString(string $phpScriptContent): string
    {
        $tokens = $this->phpTokenizer->tokenizeString($phpScriptContent);

        return $this->minifyTokens($tokens);
    }

    /** @param array<string,array<array-key,array{token:string}>> $tokens */
    private function minifyTokens(array $tokens): string
    {
        $str = '';
        foreach ($tokens as $tokensType => $tokenItems) {
            if (str_contains($tokensType, 'php')) {
                foreach ($tokenItems as $tokenItem) {
                    if (array_key_exists($tokenItem['token'], self::SPEC_SYMBOLS)) {
                        $str .= $tokenItem['token'];
                    } elseif ($tokenItem['token'] !== '') {
                        $str .= $tokenItem['token'] . ' ';
                    }
                }
            } else {
                foreach ($tokenItems as $tokenItem) {
                    $str .= $tokenItem['token'];
                }
            }
        }

        return $str;
    }
}