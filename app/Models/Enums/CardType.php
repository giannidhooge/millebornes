<?php

namespace App\Models\Enums;

enum CardType: string {
    // Card Types
    public const CARD_TYPE_DISTANCE = 'distance';
    public const CARD_TYPE_HAZARD = 'hazard';
    public const CARD_TYPE_REMEDY = 'remedy';
    public const CARD_TYPE_SAFETY = 'safety';

    // Hazard Subtypes
    public const HAZARD_ACCIDENT = 'accident';
    public const HAZARD_OUT_OF_GAS = 'out_of_gas';
    public const HAZARD_FLAT_TIRE = 'flat_tire';
    public const HAZARD_SPEED_LIMIT = 'speed_limit';
    public const HAZARD_STOP = 'stop';

    // Remedy Subtypes
    public const REMEDY_REPAIRS = 'repairs';
    public const REMEDY_GASOLINE = 'gasoline';
    public const REMEDY_SPARE_TIRE = 'spare_tire';
    public const REMEDY_END_OF_LIMIT = 'end_of_limit';
    public const REMEDY_ROLL = 'roll';

    // Safety Subtypes
    public const SAFETY_DRIVING_ACE = 'driving_ace';
    public const SAFETY_EXTRA_TANK = 'extra_tank';
    public const SAFETY_PUNCTURE_PROOF = 'puncture_proof';
    public const SAFETY_RIGHT_OF_WAY = 'right_of_way';
}