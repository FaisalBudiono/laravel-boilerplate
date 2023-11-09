<?php

namespace Tests\Unit\Rules\Enum;

use App\Rules\Enum\BackedEnumRule;
use BackedEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helper\Enum\TestBackedEnumInt;
use Tests\Helper\Enum\TestBackedEnumString;
use Tests\TestCase;

class BackedEnumRuleTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Arrange
        $service = new BackedEnumRule(TestBackedEnumString::FOO);


        // Assert
        $this->assertInstanceOf(ValidationRule::class, $service);
    }

    #[Test]
    #[DataProvider('invalidEnumDataProvider')]
    public function should_called_fail_when_key_in_by_invalid_enum(
        BackedEnum $backedEnum,
        string $errorMessage,
        string|int $mockedInput,
    ): void {
        // Arrange
        $service = new BackedEnumRule($backedEnum);


        // Act
        $service->validate('', $mockedInput, function (mixed $argMessage) use ($errorMessage) {
            $this->assertSame($errorMessage, $argMessage);
        });


        // Assert
        $this->assertSame(1, $this->getCount());
    }

    public static function invalidEnumDataProvider(): array
    {
        return [
            'backed enum string with string value' => [
                TestBackedEnumString::FOO,
                'The :attribute should only contain: foo, bar',
                'random',
            ],
            'backed enum string with int value' => [
                TestBackedEnumString::FOO,
                'The :attribute should only contain: foo, bar',
                100,
            ],

            'backed enum int with string value' => [
                TestBackedEnumInt::FOO,
                'The :attribute should only contain: 1, 102',
                'random',
            ],
            'backed enum int with string int' => [
                TestBackedEnumInt::FOO,
                'The :attribute should only contain: 1, 102',
                100,
            ],
        ];
    }

    #[Test]
    #[DataProvider('validEnumDataProvider')]
    public function should_NOT_call_fail_closure_when_value_is_valid_enum_value(
        BackedEnum $backedEnum,
        mixed $value,
    ): void {
        // Arrange
        $service = new BackedEnumRule($backedEnum);


        // Act
        $service->validate('', $value, function (mixed $argMessage) {
            $this->fail();
        });


        // Assert
        $this->expectNotToPerformAssertions();
    }

    public static function validEnumDataProvider(): array
    {
        return [
            'backed enum string #1' => [
                TestBackedEnumString::FOO,
                'bar',
            ],
            'backed enum string #2' => [
                TestBackedEnumString::FOO,
                TestBackedEnumString::FOO->value,
            ],

            'backed enum int #1' => [
                TestBackedEnumInt::FOO,
                102,
            ],
            'backed enum int #2' => [
                TestBackedEnumInt::FOO,
                TestBackedEnumInt::FOO->value,
            ],
        ];
    }
}
