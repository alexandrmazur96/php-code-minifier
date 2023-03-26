<?php

declare(strict_types=1);

namespace PhpCodeMinifier\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected const FIXTURES_DIR = __DIR__ . '/Fixtures';
}
