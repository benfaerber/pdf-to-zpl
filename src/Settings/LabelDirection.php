<?php

declare(strict_types=1);

namespace Faerber\PdfToZpl\Settings;

enum LabelDirection {
    case Up;
    case Down;
    case Left;
    case Right;

    public static function default(): self {
        return self::Up;
    }

    public function toDegree(): int {
        return match ($this) {
            self::Up => 0,
            self::Down => 180,
            self::Left => 90,
            self::Right => 270,
        };
    }
}
