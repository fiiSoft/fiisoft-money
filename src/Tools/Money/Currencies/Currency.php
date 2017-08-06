<?php

namespace FiiSoft\Tools\Money\Currencies;

use InvalidArgumentException;
use JsonSerializable;
use Money\Currency as FowlerCurrency;
use RuntimeException;
use Serializable;

abstract class Currency implements Serializable, JsonSerializable
{
    /** @var string */
    private $code;
    
    /** @var int */
    private $precision;
    
    /**
     * @param Currency|FowlerCurrency|string $code
     * @param integer $precision how many decimal digits are important
     * @throws InvalidArgumentException
     */
    public function __construct($code, $precision = 2)
    {
        if ($this->isCodeValid($code)) {
            $this->code = $code;
        } elseif ($code instanceof Currency) {
            $this->code = $code->code;
        } elseif ($code instanceof FowlerCurrency) {
            $this->code = $code->getCode();
        } else {
            throw new InvalidArgumentException('Invalid param currency code');
        }
        
        if ($this->isPrecisionValid($precision)) {
            $this->precision = $precision;
        } else {
            throw new InvalidArgumentException('Invalid param precision');
        }
    }
    
    /**
     * @param Currency|FowlerCurrency|string $other
     * @return bool
     */
    final public function equals($other)
    {
        if ($this === $other) {
            return true;
        }
    
        if ($other instanceof Currency) {
            return $this->code === $other->code;
        }
        
        if ($other instanceof FowlerCurrency) {
            return $this->code === $other->getCode();
        }
        
        if (is_string($other)) {
            return $this->code === $other;
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    final public function code()
    {
        return $this->code;
    }
    
    /**
     * @return int
     */
    final public function precision()
    {
        return $this->precision;
    }
    
    /**
     * @return string
     */
    final public function __toString()
    {
        return $this->code;
    }
    
    /**
     * @return string
     */
    final public function serialize()
    {
        return serialize([
            'code' => $this->code,
            'precision' => $this->precision,
        ]);
    }
    
    /**
     * @param string $serialized
     * @throws RuntimeException
     * @return void
     */
    final public function unserialize($serialized)
    {
        static $unserializeOption;
        if (!$unserializeOption) {
            $unserializeOption = ['allowed_classes' => false];
        }
        
        $data = unserialize($serialized, $unserializeOption);
        if (is_array($data)
            && isset($data['code'], $data['precision'])
            && $this->isCodeValid($data['code'])
            && $this->isPrecisionValid($data['precision'])
        ) {
            $this->code = $data['code'];
            $this->precision = $data['precision'];
        } else {
            throw new RuntimeException('Cannot unserialize object '.get_class($this).' because data are invalid');
        }
    }
    
    /**
     * @return string
     */
    final public function jsonSerialize()
    {
        return json_encode([
            'code' => $this->code,
            'precision' => $this->precision,
        ]);
    }
    
    /**
     * @param string $code non empty string
     * @return bool
     */
    private function isCodeValid($code)
    {
        return is_string($code) && $code !== '' && mb_strlen($code) === 3 && ctype_upper($code);
    }
    
    /**
     * @param integer $precision between 0 and 6
     * @return bool
     */
    private function isPrecisionValid($precision)
    {
        return is_int($precision) && $precision >= 0 && $precision <= 6;
    }
}