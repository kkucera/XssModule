<?php
return array(
    'tokens' => array(
        'DOCTRINE_DESK_ADAPTER' => 'EMRCore\DoctrineConnector\Adapter\Adapter',
        'DOCTRINE_DESK_CONNECTION_WRAPPER' => 'EMRCore\DoctrineConnector\AppMasterSlaveConnection',

        'DB_DESK_WRITER_HOST' => 'localhost',
        'DB_DESK_WRITER_PORT' => '3306',
        'DB_DESK_WRITER_USERNAME' => 'root',
        'DB_DESK_WRITER_PASSWORD' => '',
        'DB_DESK_WRITER_SCHEMA' => 'test',
        'DB_DESK_READER_HOST' => 'localhost',
        'DB_DESK_READER_PORT' => '3306',
        'DB_DESK_READER_USERNAME' => 'root',
        'DB_DESK_READER_PASSWORD' => '',
        'DB_DESK_READER_SCHEMA' => 'test',
        'DOCTRINE_DESK_PROXY_DIR' => sys_get_temp_dir(),

        'DESK_SUBDOMAIN' => 'webpt',
        'DESK_USERNAME' => '',
        'DESK_PASSWORD' => '',
    ),

    'service_manager' => array(
        'invokables' => array(
            'Integration\src\DeskModule\Company\CompanyDepender' => 'Integration\src\DeskModule\Company\CompanyDepender',
            'Integration\src\DeskModule\Customer\CustomerDepender' => 'Integration\src\DeskModule\Customer\CustomerDepender',
        ),
    ),
);