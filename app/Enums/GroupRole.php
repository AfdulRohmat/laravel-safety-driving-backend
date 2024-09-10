<?php

namespace App\Enums;

enum GroupRole: string
{
    case DRIVER = 'ROLE_DRIVER';
    case COMPANY = 'ROLE_COMPANY';
    case FAMILY = 'ROLE_FAMILY';
    case MEDIC = 'ROLE_MEDIC';
    case KNKT = 'ROLE_KNKT';
    case USER_GROUP = 'ROLE_USER_GROUP';
    case ADMIN_GROUP = 'ROLE_ADMIN_GROUP';
}
