<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            SubscriptionStatus::Active => 'Actif',
            SubscriptionStatus::Expired => 'Expiré',
            SubscriptionStatus::Cancelled => 'Annulé',
        };
    }
}
