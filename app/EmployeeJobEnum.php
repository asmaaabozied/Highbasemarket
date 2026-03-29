<?php

namespace App;

enum EmployeeJobEnum: string
{
    case MANAGER       = 'manager';
    case SUPERVISOR    = 'supervisor';
    case EMPLOYEE      = 'employee';
    case ADMINISTRATOR = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::MANAGER       => 'Manager',
            self::SUPERVISOR    => 'Supervisor',
            self::EMPLOYEE      => 'Employee',
            self::ADMINISTRATOR => 'Administrator',
        };
    }
}
