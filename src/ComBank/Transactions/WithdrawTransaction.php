<?php namespace ComBank\Transactions;

/**
 * Created by VS Code.
 * User: JPortugal
 * Date: 7/28/24
 * Time: 1:22 PM
 */

use ComBank\Bank\Contracts\BankAccountInterface;
use ComBank\Exceptions\InvalidOverdraftFundsException;
use ComBank\Transactions\Contracts\BankTransactionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\Traits\ConstructorTrait;

class WithdrawTransaction extends BaseTransaction implements BankTransactionInterface
{
    // amount for this transaction
    private float $amount;

    // apply a constructor to set amount
    public function __construct(float $amount)
    {
        $this->setAmount($amount);
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function applyTransaction(BankAccountInterface $bankAccount): float
    {
        $newBalance = $bankAccount->getBalance() - $this->getAmount();
        if (!$bankAccount->getOverdraft()->isGrantOverdraftFunds($newBalance)) {
            throw new InvalidOverdraftFundsException(message: 'Your withdrawal has reached the max overdraft funds.');
        }
        return $newBalance;
    }

    public function getTransactionInfo(): string {
        return "Withdrawal of " . $this->getAmount() . " applied.";
    }

    public function getAmount(): float {
        return $this->amount;
    }
}
