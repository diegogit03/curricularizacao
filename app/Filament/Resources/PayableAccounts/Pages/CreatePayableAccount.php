<?php

namespace App\Filament\Resources\PayableAccounts\Pages;

use App\Filament\Resources\PayableAccounts\PayableAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayableAccount extends CreateRecord
{
    protected static string $resource = PayableAccountResource::class;
}
