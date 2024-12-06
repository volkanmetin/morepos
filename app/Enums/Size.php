<?php

namespace App\Enums;

enum Size: string
{
    case STD = 'STD';
    case XS3 = '3XS';
    case XXS = 'XXS';
    case XS = 'XS';
    case XSS = 'XSS';
    case S = 'S';
    case SM = 'SM';
    case M = 'M';
    case ML = 'ML';
    case L = 'L';
    case LXL = 'LXL';
    case XL = 'XL';
    case XXL = 'XXL';
    case XL3 = '3XL';
    case S30 = '30';
    case S32 = '32';
    case S33 = '33';
    case S34 = '34';
    case S35 = '35';
    case S36 = '36';
    case S38 = '38';
    case S40 = '40';
    case S42 = '42';
    case S44 = '44';
    case S46 = '46';

    public function name(): string
    {
        return match ($this) {
            self::STD => 'Standart',
            self::XS3 => '3X Small',
            self::XXS => '2X Small',
            self::XS => 'XSmall',
            self::XSS => 'XSmall-Small',
            self::S => 'Small',
            self::SM => 'Small-Medium',
            self::M => 'Medium',
            self::ML => 'Medium-Large',
            self::L => 'Large',
            self::LXL => 'Large-XLarge',
            self::XL => 'XLarge',
            self::XXL => '2X Large',
            self::XL3 => '3X Large',
            self::S30 => '30 Beden',
            self::S32 => '32 Beden',
            self::S33 => '33 Beden',
            self::S34 => '34 Beden',
            self::S35 => '35 Beden',
            self::S36 => '36 Beden',
            self::S38 => '38 Beden',
            self::S40 => '40 Beden',
            self::S42 => '42 Beden',
            self::S44 => '44 Beden',
            self::S46 => '46 Beden',
            default => 'Diğer',
        };
    }

    public function nameEn(): string
    {
        return match ($this) {
            self::STD => 'Standard',
            self::XS3 => '3 Extra Small',
            self::XXS => '2 Extra Small',
            self::XS => 'Extra Small',
            self::XSS => 'Extra Small-Small',
            self::S => 'Small',
            self::SM => 'Small-Medium',
            self::M => 'Medium',
            self::ML => 'Medium-Large',
            self::L => 'Large',
            self::LXL => 'Large-Extra Large',
            self::XL => 'Extra Large',
            self::XXL => '2 Extra Large',
            self::XL3 => '3 Extra Large',
            self::S30 => 'Size 30',
            self::S32 => 'Size 32',
            self::S33 => 'Size 33',
            self::S34 => 'Size 34',
            self::S35 => 'Size 35',
            self::S36 => 'Size 36',
            self::S38 => 'Size 38',
            self::S40 => 'Size 40',
            self::S42 => 'Size 42',
            self::S44 => 'Size 44',
            self::S46 => 'Size 46',
            default => 'Other',
        };
    }
}
