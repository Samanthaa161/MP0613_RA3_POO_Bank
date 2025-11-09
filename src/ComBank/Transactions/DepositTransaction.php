<?php

namespace ComBank\Transactions;

use ComBank\Bank\Contracts\BankAccountInterface;
use ComBank\Exceptions\ZeroAmountException;
use ComBank\Transactions\Contracts\BankTransactionInterface;

class DepositTransaction implements BankTransactionInterface
{
    private float $amount;

    public function __construct(float $amount)
    {
        if ($amount <= 0) {
            throw new ZeroAmountException("Deposit amount must be greater than zero");
        }

        $this->amount = $amount;
    }

    public function applyTransaction(BankAccountInterface $account): float
    {
        // Simplemente suma el monto al saldo actual
        return $account->getBalance() + $this->amount;
    }

    public function getTransactionInfo(): string
    {
        return 'DEPOSIT_TRANSACTION';
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}
