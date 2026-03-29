<?php

namespace App\Enums;

enum RequirementType
{
    case Strength;
    case Dexterity;
    case Constitution;
    case Intelligence;
    case Wisdom;
    case Charisma;
    case Level;
    case Class;
    case Race;
}
