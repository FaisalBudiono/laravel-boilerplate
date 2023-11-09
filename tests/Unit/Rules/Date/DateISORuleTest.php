<?php

namespace Tests\Unit\Rules\Date;

use App\Rules\Date\DateISORule;
use Illuminate\Contracts\Validation\ValidationRule;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DateISORuleTest extends TestCase
{
    #[Test]
    public function should_implement_right_interface(): void
    {
        // Assert
        $this->assertInstanceOf(ValidationRule::class, $this->makeService());
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function should_called_fail_closure_when_value_is_not_valid(mixed $input): void
    {
        // Arrange
        $service = $this->makeService();


        // Act
        $service->validate('', $input, function ($argMessage) {
            $this->assertSame(
                'The :attribute should be ISO formatted datetime in UTC timezone with millisecond',
                $argMessage
            );
        });


        // Assert
        $this->assertSame(1, $this->getCount());
    }

    public static function invalidDataProvider(): array
    {
        return [
            'random string' => ['some random string'],
            'some array' => [['some random string']],
            'datetime in SQL format' => ['2022-03-31 20:00:00'],
            'datetime ISO without milliseconds' => ['2022-03-31T20:00:00Z'],
            'datetime ISO but the time is not valid' => ['2022-03-31T25:00:00.0Z'],
        ];
    }

    #[Test]
    #[DataProvider('validDataProvider')]
    public function should_NOT_call_fail_closure_when_value_has_valid_format(mixed $input): void
    {
        // Arrange
        $service = $this->makeService();


        // Act
        $service->validate('', $input, function ($argMessage) {
            $this->fail();
        });


        // Assert
        $this->expectNotToPerformAssertions();
    }

    public static function validDataProvider(): array
    {
        return [
            'in UTC' => ['2022-03-31T20:00:00.0Z'],
            'have timezone other than UTC (currently Asia/Jakarta)' => ['2022-03-31T20:00:00.0+07:00'],
        ];
    }

    protected function makeService(): DateISORule
    {
        return new DateISORule;
    }
}
