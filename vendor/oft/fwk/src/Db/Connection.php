<?php
namespace Oft\Db;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection as DbalConnection;
use Doctrine\DBAL\Driver;

class Connection extends DbalConnection
{
    protected $typeMapping = array(
        'enum' => 'string',
    );

    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null)
    {
        if (isset($params['typeMapping'])) {
            $this->typeMapping = $params['typeMapping'];
            unset($params['typeMapping']);
        }

        parent::__construct($params, $driver, $config, $eventManager);
    }

    public function connect()
    {
        $result = parent::connect();

        foreach ($this->typeMapping as $type => $alias) {
            $this->getDatabasePlatform()->registerDoctrineTypeMapping($type, $alias);
        }

        return $result;
    }
}