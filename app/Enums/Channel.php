<?php

namespace App\Enums;

enum Channel: string
{
    case Admin = 'admin';
    case Landlord = 'landlord';
    case Api = 'api';
}
