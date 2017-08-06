<?php

namespace FiiSoft\Test\Tools\Money;

use FiiSoft\Tools\Money\Currency;
use FiiSoft\Tools\Money\Money;
use Money\Currency as FowlerCurrency;
use Money\Money as FowlerMoney;

class MoneyTest extends \PHPUnit_Framework_TestCase
{
    public function test_it_can_be_created_from_FowlerMoney()
    {
        $m = $this->money($this->fowlerMoney(6539));
        
        self::assertTrue(Currency::PLN()->equals($m->currency()));
        self::assertSame('65.39', $m->toString());
        self::assertSame(65.39, $m->toFloat());
        
        self::assertTrue($m->equals(6539));
        self::assertFalse($m->equals(6540));

        self::assertTrue($m->equals(65.39));
        self::assertFalse($m->equals(65.4));

        self::assertTrue($m->equals('65.39'), $m->toString());
        self::assertFalse($m->equals('65.4'));

        self::assertTrue($m->equals($m));
        self::assertTrue($m->equals($this->money(6539, null, true)));
        self::assertTrue($m->equals($this->money(65.39)));
        self::assertTrue($m->equals($this->money('65.39')));
        self::assertTrue($m->equals($this->money($this->fowlerMoney(6539))));
    
        $other = $this->money(6539);
        self::assertSame('6539', $other->toString());
        self::assertSame(6539.0, $other->toFloat());
        
        self::assertFalse($m->equals($other));
        self::assertFalse($m->equals($this->money(6540, null, true)));
        self::assertFalse($m->equals($this->money(65.4)));
        self::assertFalse($m->equals($this->money('65.4')));
        self::assertFalse($m->equals($this->money($this->fowlerMoney(6540))));
    }
    
    public function test_it_can_be_created_from_other_Money()
    {
        $other = $this->money(45.87, Currency::PLN());
        $m = $this->money($other);
    
        self::assertTrue(Currency::PLN()->equals($m->currency()));
        self::assertSame('45.87', $m->toString());
        self::assertSame(45.87, $m->toFloat());
    
        self::assertFalse($m->equals(4587));
    
        self::assertTrue($m->equals(45.87));
        self::assertFalse($m->equals(45.88));
    
        self::assertTrue($m->equals('45.87'));
        self::assertFalse($m->equals('45.88'));
    
        self::assertTrue($m->equals($m));
        self::assertTrue($m->equals($this->money(4587, null, true)));
        self::assertTrue($m->equals($this->money(45.87)));
        self::assertTrue($m->equals($this->money('45.87')));
        self::assertTrue($m->equals($this->money($this->fowlerMoney(4587))));
    
        self::assertFalse($m->equals($this->money(4587)));
        self::assertFalse($m->equals($this->money(4588, null, true)));
        self::assertFalse($m->equals($this->money(45.88)));
        self::assertFalse($m->equals($this->money('45.88')));
        self::assertFalse($m->equals($this->money($this->fowlerMoney(4588))));
    }
    
    public function test_it_can_be_created_from_int_value()
    {
        $m = $this->money(123);

        self::assertNull($m->currency());
        self::assertSame('123', $m->toString());
        self::assertSame(123.0, $m->toFloat());

        self::assertTrue($m->equals(123));
        self::assertFalse($m->equals(62));

        self::assertTrue($m->equals(123.0));
        self::assertFalse($m->equals(62.0));

        self::assertTrue($m->equals('123'));
        self::assertFalse($m->equals('63'));

        self::assertTrue($m->equals($m));
        self::assertTrue($m->equals($this->money(123)));
        self::assertTrue($m->equals($this->money(123.0)));
        self::assertTrue($m->equals($this->money('123')));
        self::assertTrue($m->equals($this->money($this->fowlerMoney(12300))));

        self::assertFalse($m->equals($this->money(62)));
        self::assertFalse($m->equals($this->money(62.0)));
        self::assertFalse($m->equals($this->money('62')));
        self::assertFalse($m->equals($this->money($this->fowlerMoney(6200))));

        self::assertTrue($m->equals($this->fowlerMoney(12300)));
        self::assertFalse($m->equals($this->fowlerMoney(12301)));
    }

    public function test_it_can_be_created_from_float_value()
    {
        $m = $this->money(18.67);

        self::assertNull($m->currency());
        self::assertSame('18.67', $m->toString());
        self::assertSame(18.67, $m->toFloat());

        self::assertTrue($m->equals(18.67));
        self::assertFalse($m->equals(18.66));

        self::assertTrue($m->equals('18.67'));
        self::assertFalse($m->equals('18.66'));

        self::assertTrue($m->equals($m));
        self::assertTrue($m->equals($this->money(18.67)));
        self::assertTrue($m->equals($this->money('18.67')));
        self::assertTrue($m->equals($this->money(1867, null, true)));
        self::assertTrue($m->equals($this->fowlerMoney(1867)));

        self::assertFalse($m->equals($this->money(18.66)));
        self::assertFalse($m->equals($this->money('18.66')));
        self::assertFalse($m->equals($this->money(1866, null, true)));
        self::assertFalse($m->equals($this->fowlerMoney(1866)));
    }

    public function test_it_can_be_created_from_string_value()
    {
        $m = $this->money('35.42');

        self::assertNull($m->currency());
        self::assertSame('35.42', $m->toString());
        self::assertSame(35.42, $m->toFloat());

        self::assertTrue($m->equals(35.42));
        self::assertFalse($m->equals(18.66));

        self::assertTrue($m->equals('35.42'));
        self::assertFalse($m->equals('18.66'));

        self::assertTrue($m->equals($m));
        self::assertTrue($m->equals($this->money(35.42)));
        self::assertTrue($m->equals($this->money('35.42')));
        self::assertTrue($m->equals($this->money(3542, null, true)));
        self::assertTrue($m->equals($this->fowlerMoney(3542)));

        self::assertFalse($m->equals($this->money(18.66)));
        self::assertFalse($m->equals($this->money('18.66')));
        self::assertFalse($m->equals($this->money(1866, null, true)));
        self::assertFalse($m->equals($this->fowlerMoney(3543)));
    }
    
    public function test_it_can_be_created_by_factory_method()
    {
        $m = Money::from(15.78, Currency::USD());
    
        self::assertInstanceOf(Money::class, $m);
        self::assertTrue(Currency::USD()->equals($m->currency()));
        self::assertSame(15.78, $m->toFloat());
        self::assertSame('15.78', $m->toString());
        
        $other = Money::from($m);
        self::assertSame($m, $other);
        
        $p = Money::from('14.993', 3);
        self::assertSame('14.993', $p->toString());
        self::assertSame(14.993, $p->toFloat());
        
        self::assertTrue($p->equals($this->money(14.993, 3)));
        self::assertFalse($p->equals($this->money(14.993, 2)));
        
        $pln = Money::from('15.78', Currency::PLN());
        self::assertFalse($m->equals($pln));
        
        $c = Money::fromCents(1578);
        self::assertTrue($m->equals($c));
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
