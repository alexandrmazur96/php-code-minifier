<?php

declare(strict_types=1);

namespace PhpCodeMinifier;

use PhpCodeMinifier\Exceptions\IncorrectFileException;

class PhpTokenizer
{
    /**
     * Parse php file with built-in {@see token_get_all()} function and return proper code sequence.
     * @throws IncorrectFileException
     * @return array<string,array<array-key,array{token:string}>>
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
     * To be sure that we have correct tokens sequence in result we need to split them into two groups: php and html.
     * We don't minify html, so we need to keep it as is.
     *
     * Tokenize algorithm simple as that:
     * 1. We iterate through all tokens.
     * 2. If we have T_OPEN_TAG or T_OPEN_TAG_WITH_ECHO we add it to php group.
     * 3. If we have T_INLINE_HTML we add it to html group.
     * 4. Each new group starts with new index so when we iterate through result array we will keep correct sequence.
     *
     * @return array<string,array<array-key,array{token:string}>>
     */
    private function tokenizeContent(string $fileContent): array
    {
        $content = [];
        $index = 0;
        $currentContentType = null;
        foreach (token_get_all($fileContent) as $token) {
            if (is_array($token) && ($token[0] === T_OPEN_TAG || $token[0] === T_OPEN_TAG_WITH_ECHO)) {
                if ($currentContentType === 'html') {
                    $index++;
                }
                $currentContentType = 'php';
                $content['php_' . $index][] = ['token' => preg_replace('|\s+|', '', $token[1])];
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
            $tokenStr = $token[1] ?? $token;
            $content[$currentContentType . '_' . $index][] = [
                'token'  => $currentContentType === 'php' ? preg_replace('|\s+|', '', $tokenStr) : $tokenStr,
            ];
        }

        return $content;
    }
}
