<?php

namespace FiiSoft\Tools\Money\Currencies;

final class EUR extends Currency
{
    const CODE = 'EUR';
    const PRECISION = 2;
    
    public function __construct()
    {
        parent::__construct(self::CODE, self::PRECISION);
    }
}