<?php

namespace App\Services\Finance;

class ProvisionCalculator
{
    public function getProvisionRate(float $price): float
    {
        if ($price <= 350.0) {
            return 0.10;
        }

        if ($price <= 1500.0) {
            return 0.075;
        }

        return 0.03;
    }

    public function getProvisionAmount(?float $price): ?float
    {
        if ($price === null) {
            return null;
        }

        return $price * $this->getProvisionRate($price);
    }

    public function getTaxAmount(?float $provisionAmount): ?float
    {
        if ($provisionAmount === null) {
            return null;
        }

        return $provisionAmount * 0.19;
    }
}

