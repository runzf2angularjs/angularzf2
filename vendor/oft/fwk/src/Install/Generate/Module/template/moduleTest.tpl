<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>\Test;

use Oft\Mvc\Application;
use PHPUnit_Framework_TestCase;
use <?=$namespace?>\Module;

class ModuleTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Module
     */
    protected $module;

    protected function setUp()
    {
        $this->module = new Module();
    }

    public function testGetName()
    {
        $this->assertSame('<?=$moduleName?>', $this->module->getName());
    }

    public function testGetConfig()
    {
        $config = $this->module->getConfig();
        $this->assertInternalType('array', $config);
    }

    public function testGetConfigCli()
    {
        $config = $this->module->getConfig(true);
        $this->assertInternalType('array', $config);
    }

    public function testGetViewDir()
    {
        $viewDir = $this->module->getDir('views');

        $this->assertTrue(is_string($viewDir));
        $this->assertContains('views', $viewDir);
        $this->assertTrue(is_dir($viewDir));
    }

    public function testGetDir()
    {
        $dir = $this->module->getDir();

        $this->assertTrue(is_string($dir));
        $this->assertTrue(is_dir($dir));
    }

    public function testInit()
    {
        $app = new Application();
        $this->module->init($app);
    }

}
