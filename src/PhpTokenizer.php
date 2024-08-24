<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Exceptions\IncorrectFileException;

/**
 * @psalm-type _PhpToken = array{0: int, 1: string, 2: int}
 */
class PhpTokenizer
{
    private const STRING_TOKENS = [
        T_ENCAPSED_AND_WHITESPACE  => true,
        T_CONSTANT_ENCAPSED_STRING => true,
        T_STRING_VARNAME           => true,
        T_NUM_STRING               => true,
    ];

    /**
     * Parse php file with built-in {@see token_get_all()} function and return proper code sequence.
     * @return array<string,array<array-key,array{token:string}>>
     * @throws IncorrectFileException
     */
    public function tokenizeFile(string $filePath): array
    {
        $fileContent = file_get_contents($filePath);

        if ($fileContent === false) {
            throw new IncorrectFileException(sprintf('File (%s) not found.', $filePath));
        }

        return $this->tokenizeContent($fileContent);
    }

    /**
     * Parse php script with built-in {@see token_get_all()} function and return proper code sequence.
     * @return array<string,array<array-key,array{token:string}>>
     */
    public function tokenizeString(string $phpScriptContent): array
    {
        return $this->tokenizeContent($phpScriptContent);
    }

    /**
     * Removes the left-side padding from a PHP 7.3 heredoc and nowdoc.
     *
     * @param array{_PhpToken|string} $tokens
     */
    private function fixHeredocPadding(array &$tokens): void
    {
        $result = [];
        while ($token = array_shift($tokens)) {
            $result[] = $token;
            if (is_string($token) || $token[0] !== T_START_HEREDOC) {
                continue;
            }

            $docTokens = [];
            $padding = '';
            while ($docToken = array_shift($tokens)) {
                $docTokens[] = $docToken;

                if ($docToken[0] === T_END_HEREDOC) {
                    // get the left side padding of the heredoc end
                    $padding = preg_replace('|[^\s]|', '', $docToken[1]);

                    break;
                }
            }

            foreach ($docTokens as $docToken) {
                $lines = explode(PHP_EOL, $docToken[1]);
                foreach ($lines as &$line) {
                    if (str_starts_with($line, $padding)) {
                        $line = substr($line, strlen($padding));
                    }
                }
                $docToken[1] = implode(PHP_EOL, $lines);

                $result[] = $docToken;
            }
        }

        $tokens = $result;
    }

    /**
     * To be sure that we have correct tokens sequence in result we need to split them into two groups: php and html.
     * We don't minify html, so we need to keep it as is.
     *
     * Tokenize algorithm simple as that:
     * 1. We iterate through all tokens.
     * 2. If we have T_OPEN_TAG or T_OPEN_TAG_WITH_ECHO we add it to php group.
     * 3. If we have T_INLINE_HTML we add it to html group.
     * 4. Each new group starts with new index so when we iterate through result array we will keep correct sequence.
     *
     * @psalm-return array<string, list<array{token: string}>>
     */
    private function tokenizeContent(string $fileContent): array
    {
        $content = [];
        $index = 0;
        $currentContentType = '';
        $tokens = token_get_all($fileContent);
        $this->fixHeredocPadding($tokens);
        /** @var _PhpToken|string $token */
        foreach ($tokens as $token) {
            if (is_array($token) && ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO)) {
                if ($currentContentType === 'html') {
                    $index++;
                }
                $currentContentType = 'php';
                $content['php_' . $index][] = ['token' => (string)preg_replace('|\s+|', '', $token[1])];
                continue;
            }
            if (is_array($token) && $token[0] === T_INLINE_HTML) {
                if ($currentContentType === 'php') {
                    $index++;
                }
                $currentContentType = 'html';
                $content['html_' . $index][] = ['token' => $token[1]];
                continue;
            }

            if (is_array($token)) {
                $tokenStr = $token[1];
            } else {
                $tokenStr = $token;
            }

            if (trim($tokenStr) === '') {
                continue;
            }

            if ($currentContentType === 'php') {
                if (is_array($token) && $token[0] === T_COMMENT && str_starts_with($token[1], '//')) {
                    $tokenStr = '/*' . $tokenStr . '*/';
                } elseif (!array_key_exists($token[0], self::STRING_TOKENS)) {
                    $tokenStr = (string)preg_replace('|\s+|', '', $tokenStr);
                }
            }

            $content[$currentContentType . '_' . $index][] = [
                'token' => $tokenStr,
            ];
        }

        return $content;
    }
}
