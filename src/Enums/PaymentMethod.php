<?php

declare(strict_types=1);

namespace PointsApp\Points\Enums;

/**
 * Points API payment methods.
 *
 * Wire format is a numeric string ("1".."10"). The enum exposes readable names
 * but serializes to the string codes the API expects.
 */
enum PaymentMethod: string
{
    case Visa = '1';
    case Mada = '2';
    case ApplePay = '3';
    case Mastercard = '4';
    case TabbyPay = '5';
    case Dashboard = '6';
    case BankTransfer = '7';
    case Wallet = '8';
    case Cash = '9';
    case TamaraPay = '10';

    public function label(): string
    {
        return match ($this) {
            self::Visa => 'Visa',
            self::Mada => 'Mada',
            self::ApplePay => 'Apple Pay',
            self::Mastercard => 'Mastercard',
            self::TabbyPay => 'Tabby',
            self::Dashboard => 'Dashboard',
            self::BankTransfer => 'Bank Transfer',
            self::Wallet => 'Wallet',
            self::Cash => 'Cash',
            self::TamaraPay => 'Tamara',
        };
    }
}
