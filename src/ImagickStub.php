<?php

namespace Faerber\PdfToZpl;

use ReflectionClass;
use Imagick;
use ImagickPixel;
use Stringable;

/**
* Forward methods onto a class
* This is used to tell the LSP that I know the class is present
*/
abstract class Stub implements Stringable {
    private mixed $inner;

    public function __construct(mixed ...$args) {
        $klass = static::className();
        $this->inner = new $klass(...$args);
    }

    /** @return class-string */
    abstract public static function className(): string;

    /** Look up a constant */
    public static function constant(string $name): mixed {
        $klass = static::className();
        $reflector = new ReflectionClass(
            new $klass()
        );
        return $reflector->getConstant($name);
    }

    /** @param mixed[] $args */
    public function __call(string $name, array $args): mixed {
        return $this->inner->{$name}(...$args);
    }

    /** @param mixed[] $args */
    public static function __callStatic(string $name, array $args): mixed {
        $klass = static::className();
        return $klass::{$name}(...$args);
    }

    public function __get(string $name): mixed {
        return $this->inner->{$name};
    }

    public function __set(string $name, mixed $value): void {
        $this->inner->{$name} = $value;
    }

    public function __toString(): string {
        return $this->inner->__toString();
    }

    public function inner(): mixed {
        return $this->inner;
    }
}

class ImagickStub extends Stub {
    public static function className(): string {
        /** @disregard intelephense(P1009) */
        return Imagick::class;
    }
}

class ImagickPixelStub extends Stub {
    public static function className(): string {
        /** @disregard intelephense(P1009) */
        return ImagickPixel::class;
    }
}
