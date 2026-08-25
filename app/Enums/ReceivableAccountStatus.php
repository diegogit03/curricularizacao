<?php

namespace App\Enums;

enum ReceivableAccountStatus: string
{
    case Received = 'RECEIVED';
    case Pending = 'PENDING';
}
