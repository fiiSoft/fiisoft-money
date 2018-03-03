<?php

namespace FiiSoft\Test\Tools\Money;

use FiiSoft\Tools\Money\Money;
use FiiSoft\Tools\Money\MoneyFunc;
use Money\Currency as FowlerCurrency;
use Money\Money as FowlerMoney;

class MoneyFuncTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_can_compare_two_money_values()
    {
        self::assertTrue(MoneyFunc::areMoneyEqual(13, 13));
        self::assertTrue(MoneyFunc::areMoneyEqual(13.66, 13.66));
        self::assertTrue(MoneyFunc::areMoneyEqual('13.66', '13.66'));
        self::assertTrue(MoneyFunc::areMoneyEqual(13.66, '13.66'));
        self::assertTrue(MoneyFunc::areMoneyEqual(13.66, $this->money(1366, null, true)));
        self::assertTrue(MoneyFunc::areMoneyEqual($this->money('13.66'), '13.66'));
        self::assertTrue(MoneyFunc::areMoneyEqual($this->money(13.66), '13.66'));
        self::assertTrue(MoneyFunc::areMoneyEqual($this->money(13.66), $this->fowlerMoney(1366)));
        self::assertTrue(MoneyFunc::areMoneyEqual($this->fowlerMoney(1366), $this->money(1366, null, true)));
    }
    
    /**
     * @param mixed $value
     * @param mixed $currencyOrPrecision
     * @param bool $fromCents
     * @return Money
     */
    private function money($value, $currencyOrPrecision = null, $fromCents = false)
    {
        return new Money($value, $currencyOrPrecision, $fromCents);
    }
    
    /**
     * @param int $amount
     * @param string $currencyCode
     * @return FowlerMoney
     */
    private function fowlerMoney($amount, $currencyCode = 'PLN')
    {
        return new FowlerMoney($amount, new FowlerCurrency($currencyCode));
    }
    
    /**
     * @dataProvider dataForTestItCanNormaliseAmountInStringRepresentation
     *
     * @param string $amount
     * @param string $expected
     */
    public function test_it_can_normalise_amount_in_string_representation($amount, $expected)
    {
        self::assertSame($expected, MoneyFunc::normaliseStringAmount($amount));
    }
    
    /**
     * @return array
     */
    public function dataForTestItCanNormaliseAmountInStringRepresentation()
    {
        //string to normalise, expected result
        return [
            ['0,0', '0'],
            ['0.0', '0'],
            ['10', '10'],
            ['10', '10'],
            ['12,49', '12.49'],
            ['0,0500', '0.05'],
            ['9.000', '9'],
            ['1 234,456', '1234.46'],
        ];
    }
    
    /**
     * @dataProvider dataForTestItCanCastFloatAmountToStringRepresentation
     *
     * @param float $amount
     * @param string $expected
     */
    public function test_it_can_cast_float_amount_to_string_representation($amount, $expected)
    {
        self::assertSame($expected, MoneyFunc::normaliseStringAmount($amount));
    }
    
    /**
     * @return array
     */
    public function dataForTestItCanCastFloatAmountToStringRepresentation()
    {
        //float to cast, expected result
        return [
            [0, '0'],
            [0.333333333339, '0.33'],
            [5.00, '5'],
        ];
    }
    
    public function test_it_can_properly_cast_float_to_string_with_precision_zero()
    {
        self::assertSame('12300', MoneyFunc::formatAmountAsString(12300.0, 0));
        self::assertSame('12', MoneyFunc::formatAmountAsString(12.3, 0));
        self::assertSame('8', MoneyFunc::formatAmountAsString(7.87, 0));
        self::assertSame('80', MoneyFunc::formatAmountAsString(79.87, 0));
        self::assertSame('80', MoneyFunc::formatAmountAsString(80.00, 0));
    }
}
