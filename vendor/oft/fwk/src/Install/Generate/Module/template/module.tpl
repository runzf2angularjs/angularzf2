<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>;

use Oft\Module\ModuleInterface;
use Oft\Mvc\Application;

class Module implements ModuleInterface
{
    public function getName()
    {
        return '<?=$moduleName?>';
    }

    public function getConfig($cli = false)
    {
        return include __DIR__ . '/../config/config.php';
    }

    public function getDir($type = null)
    {
        $dir = __DIR__ . '/..';

        if ($type !== null) {
            $dir .= '/' . $type;
        }

        return $dir;
    }

    public function init(Application $app)
    {

    }

}
