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
}
