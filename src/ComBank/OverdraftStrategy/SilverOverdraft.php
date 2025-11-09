<?php namespace ComBank\OverdraftStrategy;

      use ComBank\OverdraftStrategy\Contracts\OverdraftInterface;

/**
 * Created by VS Code.
 * User: JPortugal
 * Date: 7/28/24
 * Time: 1:39 PM
 */

/**
 * @description: Grant 100.00 overdraft funds.
 * */
class SilverOverdraft implements OverdraftInterface
{
    private float $overdraftFundsAmount = 100.0;
    // Verifica si se permite llegar a ese balance negativo
    public function isGrantOverdraftFunds(float $balanceAfterWithdraw): bool
    {
        // Permite hasta -100.0
        return $balanceAfterWithdraw >= -$this->overdraftFundsAmount;
    }

    public function getOverdraftFundsAmount(): float
    {
        return $this->overdraftFundsAmount;
    }
}
