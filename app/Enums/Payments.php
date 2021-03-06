<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Payments extends Enum
{
    const PAYMENTDELIVERY = 1;
    const PAYMENTPAYPAL = 2;
    const PAYMENTVNPAY = 3;
    const PAYMENTONEPAY = 4;
    const PAYMENTMOMO = 5;
}
