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

namespace Oft\Test\Form\Hydrator;

use Locale;
use Oft\Form\Hydrator\FloatStrategy;
use PHPUnit_Framework_TestCase;

class FloatStrategyTest extends PHPUnit_Framework_TestCase
{

    public function testExtractWhenEmpty()
    {
        $strategy = new FloatStrategy();

        $result = $strategy->extract('');

        $this->assertNull($result);
    }

    public function testHydrateWhenEmpty()
    {
        $strategy = new FloatStrategy();

        $result = $strategy->hydrate('');

        $this->assertNull($result);
    }

    /**
     * Cas : PHP -> formulaire internationalisé (FR)
     */
    public function testExtractPhpToFr()
    {
        Locale::setDefault('fr');

        $float = 1245.209961;
        $strategy = new FloatStrategy();

        $result = $strategy->extract($float);

        $this->assertSame("1 245,209961", $result);
    }

    /**
     * Cas : formulaire internationalisé (FR) -> PHP
     */
    public function testHydrateFrToPhp()
    {
        Locale::setDefault('fr');

        $float = "1 245,209961";
        $strategy = new FloatStrategy();

        $result = $strategy->hydrate($float);

        $this->assertSame(1245.209961, $result);
    }

    /**
     * Cas : PHP -> formulaire internationalisé (EN)
     */
    public function testExtractPhpToEn()
    {
        Locale::setDefault('en');

        $float = 1245.209961;
        $strategy = new FloatStrategy();

        $result = $strategy->extract($float);

        $this->assertSame("1,245.209961", $result);
    }

    /**
     * Cas : formulaire internationalisé (EN) -> PHP
     */
    public function testHydrateEnToPhp()
    {
        Locale::setDefault('en');

        $float = "1,245.209961";
        $strategy = new FloatStrategy();

        $result = $strategy->hydrate($float);

        $this->assertSame(1245.209961, $result);
    }

    /**
     * Cas : l'hydrateur ne peut pas parser la chaîne
     */
    public function testHydrateReturnAsIs()
    {
        $float = 'not-a-float';
        $strategy = new FloatStrategy();

        $result = $strategy->hydrate($float);

        $this->assertSame($float, $result);
    }
}
