<?php

namespace FiiSoft\Tools\Money;

use FiiSoft\Tools\Money\Currencies\Currency as AbstractCurrency;
use FiiSoft\Tools\Money\Currencies\EUR;
use FiiSoft\Tools\Money\Currencies\PLN;
use FiiSoft\Tools\Money\Currencies\USD;
use InvalidArgumentException;

class Currency extends AbstractCurrency
{
    /** @var AbstractCurrency[] */
    private static $instances = [];
    
    /**
     * @param mixed $currency
     * @throws InvalidArgumentException
     * @return AbstractCurrency
     */
    public static function from($currency)
    {
        if ($currency instanceof AbstractCurrency) {
            return $currency;
        }
    
        if (is_string($currency)) {
            switch ($currency) {
                case 'PLN': return self::PLN();
                case 'USD': return self::USD();
                case 'EUR': return self::EUR();
                default:
                    return new Currency($currency);
            }
        }
    
        return new Currency($currency);
    }
    
    /**
     * @return PLN
     */
    public static function PLN()
    {
        return self::getInstance(PLN::class);
    }
    
    /**
     * @return USD
     */
    public static function USD()
    {
        return self::getInstance(USD::class);
    }
    
    /**
     * @return EUR
     */
    public static function EUR()
    {
        return self::getInstance(EUR::class);
    }
    
    /**
     * @param string $class
     * @return mixed
     */
    private static function getInstance($class)
    {
        if (isset(self::$instances[$class])) {
            return self::$instances[$class];
        }
    
        self::$instances[$class] = new $class();
        return self::$instances[$class];
    }
}