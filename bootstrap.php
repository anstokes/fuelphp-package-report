<?php

use Fuel\Core\Autoloader;

Autoloader::add_classes([
    'Anstech\Report\Controller\Report' => __DIR__ . '/classes/controller/report.php',
    'Anstech\Report\Entity\Criteria'   => __DIR__ . '/classes/entity/criteria.php',
    'Anstech\Report\Report\Report'     => __DIR__ . '/classes/report/report.php',
]);
