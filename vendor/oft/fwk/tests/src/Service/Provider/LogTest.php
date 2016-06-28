<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Test\Service\Provider;

class LoggerManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testCreateService()
    {
        $app = new \Oft\Mvc\Application(array(
           'log' => array(
                // Canal de log par défaut
                'default' => array(
                    'filename' => '/default.log',
                    'format' => array(
                        'filename' => '{date}-{filename}',
                        'date' => 'Y-m-d',
                    ),
                    'level' => 100,
                ),
                // Canal de log de sécurité pour l'OFT
                'security' => array(
                    'filename' => '/security.log',
                    'format' => array(
                        'filename' => '{date}-{filename}',
                        'date' => 'Y-m-d',
                    ),
                    'level' => 100,
                ),
            ),
        ));
        
        $provider = new \Oft\Service\Provider\Log();

        // Logger (default)
        $returnedLogger = $provider->create($app);

        // Get loggers via Monolog\Registry
        try {
            $defaultLogger = \Monolog\Registry::getInstance('default');
            $security = \Monolog\Registry::getInstance('security');
        } catch (\InvalidArgumentException $e) {
            $this->fail('Le logger devrait exister');
        }
        
        $this->assertInstanceOf('Monolog\Logger', $defaultLogger);
        $this->assertInstanceOf('Monolog\Logger', $security);

        // Logger retourné = logger par défaut
        $this->assertSame($defaultLogger, $returnedLogger);
    }

}
