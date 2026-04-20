<?php

declare(strict_types=1);

namespace Papp\Points\Enums;

/**
 * Supported currencies. The backend currently only supports SAR.
 */
enum Currency: string
{
    case SAR = 'SAR';
}
