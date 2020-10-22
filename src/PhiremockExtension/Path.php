<?php

namespace Mcustiel\Phiremock\Codeception\Extension;

use Codeception\Configuration;

class Path
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function absoluteOrRelativeToCodeceptionDir(): string
    {
        if (substr($this->path, 0, 1) === '/') {
            return $this->path;
        }
        return Configuration::projectDir() . $this->path;
    }
}
