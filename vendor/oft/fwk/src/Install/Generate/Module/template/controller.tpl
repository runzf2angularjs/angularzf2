<?php

/* @var $this Oft\View\View */

echo "<?php\n";
?>

namespace <?=$namespace?>\Controller;

use Oft\Mvc\ControllerAbstract;

class IndexController extends ControllerAbstract
{
    public function indexAction()
    {
        return array();
    }
}
