<?php

namespace App\Enums;

enum Color: string
{
    case CKR = 'CKR';
    case SYH = 'SYH';
    case BYZ = 'BYZ';
    case KMZ = 'KRM';
    case MAV = 'MAV';
    case YSL = 'YSL';
    case GRI = 'GRI';
    case BEJ = 'BEJ';
    case MOR = 'MOR';
    case TRN = 'TRN';
    case LCT = 'LCT';
    case KHV = 'KHV';
    case FUS = 'FUS';
    case PMB = 'PMB';
    case LAC = 'LAC';
    case SRI = 'SRI';
    case YMS = 'YMS';
    case EKR = 'EKR';
    case HRD = 'HRD';
    case BRD = 'BRD';
    case KRC = 'KRC';
    case ANT = 'ANT';
    case BBM = 'BBM';
    case HKY = 'HKY';
    case KRM = 'KRM';
    case VZN = 'VZN';

    public function name(): string
    {
        return match ($this) {
            self::CKR => 'Çok Renkli',
            self::SYH => 'Siyah',
            self::BYZ => 'Beyaz',
            self::KMZ => 'Kırmızı',
            self::MAV => 'Mavi',
            self::YSL => 'Yeşil',
            self::GRI => 'Gri',
            self::BEJ => 'Bej',
            self::MOR => 'Mor',
            self::TRN => 'Turuncu',
            self::LCT => 'Lacivert',
            self::KHV => 'Kahverengi',
            self::FUS => 'Fuşya',
            self::PMB => 'Pembe',
            self::LAC => 'Lila',
            self::SRI => 'Sarı',
            self::YMS => 'Yumurta sarısı',
            self::EKR => 'Ekru',
            self::HRD => 'Hardal',
            self::BRD => 'Bordo',
            self::KRC => 'Kırçıllı',
            self::ANT => 'Antrasit',
            self::BBM => 'Bebe Mavisi',
            self::HKY => 'Haki Yeşili',
            self::KRM => 'Krem',
            self::VZN => 'Vizon',
            default => 'Diğer',
        };
    }

    public function nameEn(): string
    {
        return match ($this) {
            self::CKR => 'Multi-Color',
            self::SYH => 'Black',
            self::BYZ => 'White',
            self::KMZ => 'Red',
            self::MAV => 'Blue',
            self::YSL => 'Green',
            self::GRI => 'Gray',
            self::BEJ => 'Beige',
            self::MOR => 'Purple',
            self::TRN => 'Orange',
            self::LCT => 'Navy Blue',
            self::KHV => 'Brown',
            self::FUS => 'Fuchsia',
            self::PMB => 'Pink',
            self::LAC => 'Lilac',
            self::SRI => 'Yellow',
            self::YMS => 'Egg Yolk',
            self::EKR => 'Ecru',
            self::HRD => 'Mustard',
            self::BRD => 'Burgundy',
            self::KRC => 'Mottled',
            self::ANT => 'Antrasit',
            self::BBM => 'Baby Blue',
            self::HKY => 'Haki Green',
            self::KRM => 'Cream',
            self::VZN => 'Ivory',
            default => 'Other',
        };
    }

    public function hexCode(): string
    {
        return match ($this) {
            self::CKR => '#77e9ad',
            self::SYH => '#000000',
            self::BYZ => '#FFFFFF',
            self::KMZ => '#FF0000',
            self::MAV => '#0000FF',
            self::YSL => '#008000',
            self::GRI => '#808080',
            self::BEJ => '#F5F5DC',
            self::MOR => '#800080',
            self::TRN => '#FFA500',
            self::LCT => '#000080',
            self::KHV => '#A52A2A',
            self::FUS => '#FF00FF',
            self::PMB => '#FFC0CB',
            self::LAC => '#C8A2C8',
            self::SRI => '#FFFF00',
            self::YMS => '#FFD700',
            self::EKR => '#F3E5AB',
            self::HRD => '#FFDB58',
            self::BRD => '#800020',
            self::KRC => '#D3D3D3',
            self::ANT => '#808080',
            self::BBM => '#E0FFFF',
            self::HKY => '#9F9F5F',
            self::KRM => '#F0E68C',
            self::VZN => '#ebc8b2',
            default => '',
        };
    }
}
