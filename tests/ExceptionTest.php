<?php

namespace Suitcase;

use PHPUnit\Framework\TestCase;
use Suitcase\Exception\ExceptionBase;

class ExceptionTest extends TestCase
{
    /**
     * Test that the error message is stored.
     */
    public function testErrorMessage(): void
    {
        $e = new ExceptionBase('Message', 'Error');
        $this->assertEquals('Error', $e->getError());
    }
}
