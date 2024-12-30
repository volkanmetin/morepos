<?php

namespace App\Enums;

enum SettingKey: string
{
    // Firma Bilgileri
    case COMPANY_NAME = 'company_name';
    case COMPANY_ADDRESS = 'company_address';
    case COMPANY_PHONE = 'company_phone';
    case COMPANY_EMAIL = 'company_email';
    case COMPANY_TAX_OFFICE = 'company_tax_office';
    case COMPANY_TAX_NUMBER = 'company_tax_number';

    // Satış Ayarları
    case TAX_RATE = 'tax_rate';
    case DEFAULT_PAYMENT_METHOD = 'default_payment_method';
    case ALLOW_NEGATIVE_STOCK = 'allow_negative_stock';
    case SHOW_STOCK_WARNING = 'show_stock_warning';
    case STOCK_WARNING_LIMIT = 'stock_warning_limit';

    // Fatura Ayarları
    case INVOICE_PREFIX = 'invoice_prefix';
    case INVOICE_STARTING_NUMBER = 'invoice_starting_number';
    case INVOICE_FOOTER_TEXT = 'invoice_footer_text';

    public static function getGroups(): array
    {
        return [
            'company' => [
                'title' => 'Firma Bilgileri',
                'settings' => [
                    self::COMPANY_NAME->value => [
                        'title' => 'Firma Adı',
                        'type' => 'text',
                        'default' => '',
                    ],
                    self::COMPANY_ADDRESS->value => [
                        'title' => 'Firma Adresi',
                        'type' => 'textarea',
                        'default' => '',
                    ],
                    self::COMPANY_PHONE->value => [
                        'title' => 'Firma Telefonu',
                        'type' => 'text',
                        'default' => '',
                    ],
                    self::COMPANY_EMAIL->value => [
                        'title' => 'Firma E-posta',
                        'type' => 'email',
                        'default' => '',
                    ],
                    self::COMPANY_TAX_OFFICE->value => [
                        'title' => 'Vergi Dairesi',
                        'type' => 'text',
                        'default' => '',
                    ],
                    self::COMPANY_TAX_NUMBER->value => [
                        'title' => 'Vergi Numarası',
                        'type' => 'text',
                        'default' => '',
                    ],
                ],
            ],
            'sales' => [
                'title' => 'Satış Ayarları',
                'settings' => [
                    self::TAX_RATE->value => [
                        'title' => 'KDV Oranı (%)',
                        'type' => 'number',
                        'default' => '18',
                    ],
                    self::DEFAULT_PAYMENT_METHOD->value => [
                        'title' => 'Varsayılan Ödeme Yöntemi',
                        'type' => 'select',
                        'options' => [
                            'cash' => 'Nakit',
                            'pos1' => 'POS 1',
                            'pos2' => 'POS 2',
                        ],
                        'default' => 'cash',
                    ],
                    self::ALLOW_NEGATIVE_STOCK->value => [
                        'title' => 'Negatif Stoka İzin Ver',
                        'type' => 'boolean',
                        'default' => 'false',
                    ],
                    self::SHOW_STOCK_WARNING->value => [
                        'title' => 'Stok Uyarısı Göster',
                        'type' => 'boolean',
                        'default' => 'true',
                    ],
                    self::STOCK_WARNING_LIMIT->value => [
                        'title' => 'Stok Uyarı Limiti',
                        'type' => 'number',
                        'default' => '10',
                    ],
                ],
            ],
            'invoice' => [
                'title' => 'Fatura Ayarları',
                'settings' => [
                    self::INVOICE_PREFIX->value => [
                        'title' => 'Fatura Öneki',
                        'type' => 'text',
                        'default' => 'INV',
                    ],
                    self::INVOICE_STARTING_NUMBER->value => [
                        'title' => 'Fatura Başlangıç Numarası',
                        'type' => 'number',
                        'default' => '1',
                    ],
                    self::INVOICE_FOOTER_TEXT->value => [
                        'title' => 'Fatura Alt Metni',
                        'type' => 'textarea',
                        'default' => '',
                    ],
                ],
            ],
        ];
    }
} 