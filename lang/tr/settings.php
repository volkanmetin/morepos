<?php

return [
    'title' => 'Ayarlar',
    'save' => 'Kaydet',
    'saving' => 'Kaydediliyor...',
    'saved' => 'Ayarlar başarıyla kaydedildi.',

    'groups' => [
        'company' => [
            'title' => 'Firma Bilgileri',
            'name' => 'Firma Adı',
            'address' => 'Firma Adresi',
            'phone' => 'Firma Telefonu',
            'email' => 'Firma E-posta',
            'tax_office' => 'Vergi Dairesi',
            'tax_number' => 'Vergi Numarası',
        ],
        'sales' => [
            'title' => 'Satış Ayarları',
            'tax_rate' => 'KDV Oranı (%)',
            'default_payment_method' => 'Varsayılan Ödeme Yöntemi',
            'allow_negative_stock' => 'Negatif Stoka İzin Ver',
            'show_stock_warning' => 'Stok Uyarısı Göster',
            'stock_warning_limit' => 'Stok Uyarı Limiti',
        ],
        'invoice' => [
            'title' => 'Fatura Ayarları',
            'prefix' => 'Fatura Öneki',
            'starting_number' => 'Fatura Başlangıç Numarası',
            'footer_text' => 'Fatura Alt Metni',
        ],
    ],

    'payment_methods' => [
        'cash' => 'Nakit',
        'pos1' => 'POS 1',
        'pos2' => 'POS 2',
    ],
]; 