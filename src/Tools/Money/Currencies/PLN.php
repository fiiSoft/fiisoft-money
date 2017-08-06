<?php

namespace FiiSoft\Tools\Money\Currencies;

final class PLN extends Currency
{
    const CODE = 'PLN';
    const PRECISION = 2;
    
    public function __construct()
    {
        parent::__construct(self::CODE, self::PRECISION);
    }
}