<?php

namespace FiiSoft\Tools\Money;

use Exception;
use FiiSoft\Tools\Money as FiiSoftMoney;
use InvalidArgumentException;
use LogicException;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money as FowlerMoney;
use OutOfRangeException;

final class Money
{
    private static $SOURCE_INT = 1;
    private static $SOURCE_FLOAT = 2;
    private static $SOURCE_STRING = 3;
    private static $SOURCE_MONEY = 4;
    
    /** @var DecimalMoneyFormatter */
    private static $moneyFormatter;
    
    /** @var Currencies\Currency|null */
    private $currency;
    
    /** @var int */
    private $source;
    
    /** @var int|null */
    private $intAmount;
    
    /** @var float|null */
    private $floatAmount;
    
    /** @var string|null numeric */
    private $stringAmount;
    
    /** @var FowlerMoney|null */
    private $moneyAmount;
    
    /** @var bool */
    private $fromCents = false;
    
    /** @var int */
    private $precision = 2;
    
    /**
     * @param mixed $amount
     * @param FiiSoftMoney\Currencies\Currency|\Money\Currency|int|null $precisionOrCurrency
     * @throws InvalidArgumentException
     * @return Money
     */
    public static function from($amount, $precisionOrCurrency = null)
    {
        if ($amount instanceof Money) {
            return $amount;
        }
    
        if ($amount === null) {
            throw new InvalidArgumentException('Invalid argument amount');
        }
        
        return new static($amount, $precisionOrCurrency, false);
    }
    
    /**
     * @param int $amount
     * @param FiiSoftMoney\Currencies\Currency|\Money\Currency|int|null $precisionOrCurrency
     * @throws InvalidArgumentException
     * @return Money
     */
    public static function fromCents($amount, $precisionOrCurrency = null)
    {
        if (is_int($amount)) {
            return new static($amount, $precisionOrCurrency, true);
        }
        
        throw new InvalidArgumentException('Invalid argument amount');
    }
    
    /**
     * @param Money|FowlerMoney|string|float|int $amount
     * @param Currencies\Currency|\Money\Currency|string|int|null $currencyOrPrecision
     * @param bool $fromCents
     * @throws InvalidArgumentException
     */
    public function __construct($amount, $currencyOrPrecision = null, $fromCents = false)
    {
        if ($amount instanceof Money) {
            $this->fromCents = $amount->fromCents;
            $this->currency = $amount->currency;
            $this->source = $amount->source;
            $this->stringAmount = $amount->stringAmount;
            $this->floatAmount = $amount->floatAmount;
            $this->intAmount = $amount->intAmount;
            $this->moneyAmount = $amount->moneyAmount;
            $this->precision = $amount->precision;
        } elseif ($amount instanceof FowlerMoney) {
            $this->fromCents = true;
            $this->stringAmount = $amount->getAmount();
            $this->moneyAmount = $amount;
            $this->source = self::$SOURCE_MONEY;
            $this->currency = Currency::from($amount->getCurrency());
        } else {
            $this->fromCents = (bool) $fromCents;
    
            if (is_int($currencyOrPrecision)) {
                if ($currencyOrPrecision < 0 || $currencyOrPrecision > 6) {
                    throw new InvalidArgumentException('Invalid argument currencyOrPrrecision');
                }
                $this->precision = $currencyOrPrecision;
            } elseif ($currencyOrPrecision !== null) {
                $this->currency = Currency::from($currencyOrPrecision);
                $this->precision = $this->currency->precision();
            }
            
            if (is_int($amount)) {
                $this->intAmount = $amount;
                $this->source = self::$SOURCE_INT;
            } elseif ($this->fromCents) {
                throw new InvalidArgumentException('Invalid argument amount');
            } elseif (is_float($amount)) {
                $this->floatAmount = round($amount, $this->precision);
                $this->source = self::$SOURCE_FLOAT;
            } elseif (is_numeric($amount)) {
                $this->stringAmount = $amount;
                $this->source = self::$SOURCE_STRING;
            } else {
                throw new InvalidArgumentException('Invalid argument amount');
            }
        }
    }
    
    /**
     * @return Currencies\Currency|null
     */
    public function currency()
    {
        return $this->currency;
    }
    
    /**
     * @param mixed $other
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     * @throws LogicException
     * @return bool
     */
    public function equals($other)
    {
        return $this === $other ?: $this->isSameAmount($other) && $this->isSameCurrency($other);
    }
    
    /**
     * @param mixed $other
     * @throws InvalidArgumentException
     * @throws OutOfRangeException
     * @throws LogicException
     * @return bool
     */
    private function isSameAmount($other)
    {
        if (is_int($other)) {
            return $this->checkOtherInt($other);
        }
        
        if (is_float($other)) {
            return $this->checkOtherFloat($other);
        }
    
        if (is_numeric($other)) {
            return $this->checkOtherNumeric($other);
        }
        
        if ($other instanceof Money) {
            return $this->checkOtherMoney($other);
        }
    
        if ($other instanceof FowlerMoney) {
            return $this->checkOtherFowlerMoney($other);
        }
    
        if ($other === null) {
            return false;
        }
    
        $message = 'Invalid param amount in method ' . __FUNCTION__;
        if (is_object($other)) {
            $message .= ' - param other is an object of type '.get_class($other);
        } else {
            $message .= ' - param has type '.gettype($other).' and value "'.$other.'"';
        }
        
        throw new InvalidArgumentException($message);
    }
    
    /**
     * @param int $other
     * @throws OutOfRangeException
     * @throws LogicException
     * @return bool
     */
    private function checkOtherInt($other)
    {
        if ($this->intAmount !== null) {
            if ($this->fromCents) {
                return (string) $other === $this->intAmount . str_repeat('0', $this->precision);
            }
            return $this->intAmount === $other;
        }
    
        if ($this->moneyAmount !== null) {
            if ($this->fromCents) {
                return $this->stringAmount === (string) $other;
            }
        }
        
        if ($this->floatAmount !== null) {
            return $this->hasFloatAmount((float) $other);
        }
        
        if ($this->stringAmount !== null) {
            if ($this->fromCents) {
                $this->floatAmount = ((float) $this->stringAmount) / 10.0 ** $this->precision;
            } else {
                $this->floatAmount = (float) $this->stringAmount;
            }
            return $this->hasFloatAmount((float) $other);
        }
        
        throw new LogicException('This line should be never reached');
    }
    
    /**
     * @param float $other
     * @throws LogicException
     * @return bool
     */
    private function checkOtherFloat($other)
    {
        if ($this->floatAmount === null) {
            if ($this->intAmount !== null) {
                if ($this->fromCents) {
                    $this->floatAmount = $this->intAmount / 10.0 ** $this->precision;
                } else {
                    $this->floatAmount = (float) $this->intAmount;
                }
            } elseif ($this->stringAmount !== null) {
                if ($this->fromCents) {
                    $this->floatAmount = ((float) $this->stringAmount) / 10.0 ** $this->precision;
                } else {
                    $this->floatAmount = (float) $this->stringAmount;
                }
            } else {
                throw new LogicException('This line should be never reached');
            }
        }
    
        return $this->hasFloatAmount($other);
    }
    
    /**
     * @param string $other
     * @throws LogicException
     * @return bool
     */
    private function checkOtherNumeric($other)
    {
        if ($this->stringAmount !== null && !$this->fromCents) {
            return $this->stringAmount === $other;
        }
        
        if ($this->floatAmount === null) {
            if ($this->stringAmount !== null) {
                if ($this->fromCents) {
                    $this->floatAmount = ((float) $this->stringAmount) / 10.0 ** $this->precision;
                } else {
                    $this->floatAmount = (float) $this->stringAmount;
                }
            } elseif ($this->intAmount !== null) {
                if ($this->fromCents) {
                    $this->floatAmount = $this->intAmount / 10.0 ** $this->precision;
                } else {
                    $this->floatAmount = (float) $this->intAmount;
                }
            } else {
                throw new LogicException('This line should be never reached');
            }
        }
    
        return $this->hasFloatAmount((float) $other);
    }
    
    /**
     * @param Money $other
     * @throws LogicException
     * @return bool
     */
    private function checkOtherMoney(Money $other)
    {
        if ($this->source === $other->source) {
            if ($other->source === self::$SOURCE_INT) {

                if (!($this->fromCents XOR $other->fromCents)) {
                    return $this->intAmount === $other->intAmount;
                }

                $my = (string) $this->intAmount;
                $his = (string) $other->intAmount;

                if ($this->fromCents) {
                    return $my === $his.str_repeat('0', $this->precision);
                }

                return $his === $my.str_repeat('0', $other->precision);
            }

            if ($other->source === self::$SOURCE_FLOAT) {
                return $this->hasFloatAmount($other->floatAmount);
            }

            if ($other->source === self::$SOURCE_STRING) {
                return $this->checkOtherNumeric($other->stringAmount);
            }

            if ($other->source === self::$SOURCE_MONEY) {
                return $this->stringAmount === $other->stringAmount;
            }

            throw new LogicException('This line should be never reached');
        }
    
        if ($other->source === self::$SOURCE_INT) {
            if ($other->fromCents) {
                return $this->checkOtherFloat($other->intAmount / 10.0 ** $other->precision);
            }
    
            if ($this->fromCents) {
                return $this->checkOtherNumeric($other->intAmount . str_repeat('0', $other->precision));
            }
            
            return $this->checkOtherInt($other->intAmount);
        }

        if ($other->source === self::$SOURCE_FLOAT) {
            return $this->checkOtherFloat($other->floatAmount);
        }

        if ($other->source === self::$SOURCE_STRING) {
            return $this->checkOtherNumeric($other->stringAmount);
        }

        if ($other->source === self::$SOURCE_MONEY) {
            if ($this->fromCents) {
                return $other->stringAmount === (string) $this->intAmount;
            }

            return $this->checkOtherFloat($other->stringAmount / 10.0 ** $other->precision);
        }
    
        throw new LogicException('This line should be never reached');
    }
    
    /**
     * @param FowlerMoney $other
     * @return bool
     * @throws LogicException
     */
    private function checkOtherFowlerMoney(FowlerMoney $other)
    {
        if ($this->moneyAmount !== null) {
            return $this->moneyAmount === $other->getAmount();
        }
    
        if ($this->intAmount !== null) {
            if ($this->fromCents) {
                return $other->getAmount() === (string) $this->intAmount;
            }
            return $other->getAmount() === $this->intAmount . str_repeat('0', $this->precision);
        }
    
        if ($this->floatAmount !== null) {
            return $other->getAmount() === (string) ($this->floatAmount * 10.0 ** $this->precision);
        }
        
        if ($this->stringAmount !== null) {
            return $other->getAmount() === (string) ((float) $this->stringAmount * 10.0 ** $this->precision);
        }
        
        throw new LogicException('This line should be never reached');
    }
    
    /**
     * @param mixed $other
     * @return bool
     */
    private function isSameCurrency($other)
    {
        if (!$this->currency) {
            return true;
        }
        
        if ($other instanceof Money) {
            if ($other->currency) {
                return $this->currency->equals($other->currency);
            }
            return true;
        }
    
        if ($other instanceof FowlerMoney) {
            return $this->currency->equals($other->getCurrency());
        }
        
        return true;
    }
    
    /**
     * @param float $val
     * @return bool
     */
    private function canCastFloatToInt($val)
    {
        return $this->areFloatsEqual($val, (float) ((int) $val));
    }
    
    /**
     * @param float $value
     * @return bool
     */
    private function hasFloatAmount($value)
    {
        return abs($this->floatAmount - $value) < 0.0000000001;
    }
    
    /**
     * @param float $left
     * @param float $right
     * @return bool
     */
    private function areFloatsEqual($left, $right)
    {
        return abs($left - $right) < 0.0000000001;
    }
    
    /**
     * @throws LogicException
     * @return string
     */
    public function toString()
    {
        if ($this->moneyAmount !== null) {
            return $this->decimalMoneyFormatter()->format($this->moneyAmount);
        }
        
        if ($this->stringAmount !== null) {
//            if ($this->fromCents) {
//                //TODO finish it....
//            }
            
            return $this->stringAmount;
        }
    
        if ($this->floatAmount !== null) {
            return MoneyFunc::formatAmountAsString($this->floatAmount, $this->precision);
        }
    
        if ($this->intAmount !== null) {
            if ($this->fromCents) {
                $this->stringAmount = (string) ($this->intAmount / 10.0 ** $this->precision);
            } else {
                $this->stringAmount = (string) $this->intAmount;
            }
            
            return $this->stringAmount;
        }
    
        throw new LogicException('This line should be never reached');
    }
    
    /**
     * @throws LogicException
     * @return float
     */
    public function toFloat()
    {
        if ($this->floatAmount !== null) {
            return $this->floatAmount;
        }
    
        if ($this->intAmount !== null) {
            if ($this->fromCents) {
                $this->floatAmount = $this->intAmount / 10.0 ** $this->precision;
            } else {
                $this->floatAmount = (float) $this->intAmount;
            }
            return $this->floatAmount;
        }
    
        if ($this->stringAmount !== null) {
            if ($this->fromCents) {
                $this->floatAmount = ((float) $this->stringAmount) / 10.0 ** $this->precision;
            } else {
                $this->floatAmount = (float) $this->stringAmount;
            }
            return $this->floatAmount;
        }
    
        throw new LogicException('This line should be never reached');
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->toString();
        } catch (Exception $e) {
            return __METHOD__.' exception: ['.$e->getCode().'] '.$e->getMessage();
        }
    }
    
    /**
     * @return DecimalMoneyFormatter
     */
    private function decimalMoneyFormatter()
    {
        if (!self::$moneyFormatter) {
            self::$moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
        }
        
        return self::$moneyFormatter;
    }
}