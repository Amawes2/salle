<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case MobileMoney = 'mobile_money';
    case Card = 'card';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            PaymentMethod::Cash => 'Espèces',
            PaymentMethod::MobileMoney => 'Mobile Money',
            PaymentMethod::Card => 'Carte bancaire',
            PaymentMethod::BankTransfer => 'Virement',
        };
    }
}
