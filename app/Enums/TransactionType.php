<?php

namespace App\Enums;

enum TransactionType: string
{
    case Revenue = 'REVENUE';
    case Expense = 'EXPENSE';
}
