<?php

namespace ComBank\Bank;

use ComBank\Exceptions\BankAccountException;
use ComBank\Exceptions\InvalidOverdraftFundsException;
use ComBank\Exceptions\FailedTransactionException;
use ComBank\Exceptions\ZeroAmountException;
use ComBank\OverdraftStrategy\Contracts\OverdraftInterface;
use ComBank\OverdraftStrategy\NoOverdraft;
use ComBank\Transactions\Contracts\BankTransactionInterface;
use ComBank\Transactions\DepositTransaction;
use ComBank\Transactions\WithdrawTransaction;

class BankAccount implements \ComBank\Bank\Contracts\BankAccountInterface
{
    private float $balance;
    private string $status;
    private OverdraftInterface $overdraft;
    
    // Constructor
    public function __construct(float $balance = 400.0, ?OverdraftInterface $overdraftStrategy = null)
    {
        $this->balance = $balance;
        $this->status = 'open';
        $this->overdraft = $overdraftStrategy ?? new NoOverdraft();
    }

    public function transaction(BankTransactionInterface $transaction): void
    {
        if (!$this->isOpen()) {
            throw new BankAccountException("Bank account should be opened");
        }

        if ($transaction instanceof DepositTransaction) {
            $this->balance += $transaction->getAmount();
            return;
        }

        // Retiros
        if ($transaction instanceof WithdrawTransaction) {
            $amount = $transaction->getAmount();

            // Si el balance es suficiente
            if ($this->balance >= $amount) {
                $this->balance -= $amount;
                return;
            }

            // Si hay overdraft configurado
            if ($this->overdraft) {
                $newBalance = $this->balance - $amount;

                if ($this->overdraft->isGrantOverdraftFunds($newBalance)) {
                    $this->balance = $newBalance;
                    return;
                }

                throw new FailedTransactionException("Your withdrawal has reached the max overdraft funds.");
            }
        }
    }

    // con esto sabremos si la cuenta está abierta
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    // Reabrir cuenta
    public function reopenAccount(): void
    {
        if ($this->isOpen()) {
        throw new BankAccountException("Cannot reopen an already open account.");
        }
        $this->status = 'open';
    }

    // Cerrar cuenta
    public function closeAccount(): void
    {
        $this->status = 'closed';
    }

    // Getters y setters
    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        if ($balance < 0 && !$this->overdraft->isGrantOverdraftFunds($balance)) {
            throw new ZeroAmountException("Saldo no puede ser negativo sin autorización de sobregiro");
        }
        $this->balance = $balance;
    }

    // Overdraft
    public function applyOverdraft(OverdraftInterface $overdraft): void
    {
        $this->overdraft = $overdraft;
    }

    public function getOverdraft(): OverdraftInterface
    {
        return $this->overdraft ?? new NoOverdraft();
    }
}
