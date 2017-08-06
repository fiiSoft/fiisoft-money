<?php

namespace FiiSoft\Tools\Money\Currencies;

final class USD extends Currency
{
    const CODE = 'USD';
    const PRECISION = 2;
    
    public function __construct()
    {
        parent::__construct(self::CODE, self::PRECISION);
    }
}