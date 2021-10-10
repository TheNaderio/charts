<?php

declare(strict_types=1);

use Hoogi91\Charts\Domain\Model\ChartData;
use Hoogi91\Charts\Domain\Model\ChartDataPlain;
use Hoogi91\Charts\Domain\Model\ChartDataSpreadsheet;

return [
    ChartData::class => [
        'subclasses' => [
            ChartDataPlain::class,
            ChartDataSpreadsheet::class,
        ]
    ],
    ChartDataPlain::class => [
        'tableName' => 'tx_charts_domain_model_chartdata',
        'recordType' => 0,
    ],
    ChartDataSpreadsheet::class => [
        'tableName' => 'tx_charts_domain_model_chartdata',
        'recordType' => 1,
        'properties' => [
            'labels' => [
                'fieldName' => 'spreadsheet_labels'
            ],
            'datasets' => [
                'fieldName' => 'spreadsheet_datasets'
            ],
            'datasetsLabels' => [
                'fieldName' => 'spreadsheet_datasets_labels'
            ],
            'assets' => [
                'fieldName' => 'spreadsheet_assets'
            ],
        ],
    ],
];
