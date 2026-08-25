<?php

namespace App\Enums;

enum PayableAccountStatus: string
{
    case Paid = 'PAID';
    case Pending = 'PENDING';
}
