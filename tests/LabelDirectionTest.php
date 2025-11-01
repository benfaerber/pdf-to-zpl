<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Settings\LabelDirection;
use PHPUnit\Framework\TestCase;

final class LabelDirectionTest extends TestCase {
    public function testLabelDirectionIsEnum(): void {
        $reflectionClass = new ReflectionClass(LabelDirection::class);

        $this->assertTrue($reflectionClass->isEnum());
    }

    public function testLabelDirectionHasUpCase(): void {
        $this->assertTrue(enum_exists(LabelDirection::class));
        $this->assertNotNull(LabelDirection::Up);
    }

    public function testLabelDirectionHasDownCase(): void {
        $this->assertTrue(enum_exists(LabelDirection::class));
        $this->assertNotNull(LabelDirection::Down);
    }

    public function testLabelDirectionHasLeftCase(): void {
        $this->assertTrue(enum_exists(LabelDirection::class));
        $this->assertNotNull(LabelDirection::Left);
    }

    public function testLabelDirectionHasRightCase(): void {
        $this->assertTrue(enum_exists(LabelDirection::class));
        $this->assertNotNull(LabelDirection::Right);
    }

    public function testUpReturnsZeroDegrees(): void {
        $this->assertEquals(0, LabelDirection::Up->toDegree());
    }

    public function testDownReturns180Degrees(): void {
        $this->assertEquals(180, LabelDirection::Down->toDegree());
    }

    public function testLeftReturns90Degrees(): void {
        $this->assertEquals(90, LabelDirection::Left->toDegree());
    }

    public function testRightReturns270Degrees(): void {
        $this->assertEquals(270, LabelDirection::Right->toDegree());
    }

    public function testDefaultReturnsUp(): void {
        $this->assertEquals(LabelDirection::Up, LabelDirection::default());
    }

    public function testDefaultReturnsZeroDegrees(): void {
        $default = LabelDirection::default();
        $this->assertEquals(0, $default->toDegree());
    }

    public function testAllCasesAreDistinct(): void {
        $this->assertNotEquals(LabelDirection::Up, LabelDirection::Down);
        $this->assertNotEquals(LabelDirection::Up, LabelDirection::Left);
        $this->assertNotEquals(LabelDirection::Up, LabelDirection::Right);
        $this->assertNotEquals(LabelDirection::Down, LabelDirection::Left);
        $this->assertNotEquals(LabelDirection::Down, LabelDirection::Right);
        $this->assertNotEquals(LabelDirection::Left, LabelDirection::Right);
    }

    public function testAllDegreesAreDistinct(): void {
        $degrees = [
            LabelDirection::Up->toDegree(),
            LabelDirection::Down->toDegree(),
            LabelDirection::Left->toDegree(),
            LabelDirection::Right->toDegree(),
        ];

        $uniqueDegrees = array_unique($degrees);

        $this->assertCount(4, $uniqueDegrees);
    }

    public function testToDegreesReturnsIntegers(): void {
        $this->assertIsInt(LabelDirection::Up->toDegree());
        $this->assertIsInt(LabelDirection::Down->toDegree());
        $this->assertIsInt(LabelDirection::Left->toDegree());
        $this->assertIsInt(LabelDirection::Right->toDegree());
    }

    public function testEnumCanBeUsedInMatch(): void {
        $result = match (LabelDirection::Up) {
            LabelDirection::Up => 'up',
            LabelDirection::Down => 'down',
            LabelDirection::Left => 'left',
            LabelDirection::Right => 'right',
        };

        $this->assertEquals('up', $result);
    }

    public function testAllDirectionsCanBeMatchedToDegrees(): void {
        $directions = [
            LabelDirection::Up,
            LabelDirection::Down,
            LabelDirection::Left,
            LabelDirection::Right,
        ];

        foreach ($directions as $direction) {
            $degree = $direction->toDegree();
            $this->assertIsInt($degree);
            $this->assertGreaterThanOrEqual(0, $degree);
            $this->assertLessThan(360, $degree);
        }
    }

    public function testDegreesAreValidRotations(): void {
        $validRotations = [0, 90, 180, 270];

        $this->assertContains(LabelDirection::Up->toDegree(), $validRotations);
        $this->assertContains(LabelDirection::Down->toDegree(), $validRotations);
        $this->assertContains(LabelDirection::Left->toDegree(), $validRotations);
        $this->assertContains(LabelDirection::Right->toDegree(), $validRotations);
    }
}
