<?php

namespace FiiSoft\Tools\Money;

use InvalidArgumentException;
use LogicException;
use Money\Money as FowlerMoney;
use OutOfRangeException;

final class MoneyFunc
{
    /**
     * @param mixed $first
     * @param mixed $second
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     * @throws LogicException
     * @return bool
     */
    public static function areMoneyEqual($first, $second)
    {
        if ($first === null || $second === null) {
            return false;
        }
    
        if ($first === $second) {
            return true;
        }
    
        if ($first instanceof Money) {
            return $first->equals($second);
        }
        
        if ($second instanceof Money) {
            return $second->equals($first);
        }
    
        if ($first instanceof FowlerMoney && $second instanceof FowlerMoney) {
            return $first->equals($second);
        }
        
        return Money::from($first)->equals($second);
    }
    
    /**
     * @param mixed $first
     * @param mixed $second
     * @throws InvalidArgumentException
     * @return bool
     */
    public static function areCurrenciesTheSame($first, $second)
    {
        if ($first === null || $second === null) {
            return false;
        }
        
        return $first === $second ?: Currency::from($first)->equals(Currency::from($second));
    }
}