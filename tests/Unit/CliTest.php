<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Tests\Unit;

use PhpCodeMinifier\Tests\TestCase;

final class CliTest extends TestCase
{
    /** @psalm-suppress ForbiddenCode */
    public function testWritesOnlyMinifiedCodeToStandardOutput(): void
    {
        $command = sprintf(
            '%s %s --from-content=%s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(dirname(__DIR__, 2) . '/bin/php-code-minifier'),
            escapeshellarg('<?php echo 1;')
        );

        $this->assertSame('<?php echo 1;', shell_exec($command));
    }
}
