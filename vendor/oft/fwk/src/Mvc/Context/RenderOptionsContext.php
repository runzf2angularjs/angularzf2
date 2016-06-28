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

namespace Oft\Mvc\Context;

use ArrayObject;
use Oft\View\Model;
use Zend\View\Model\ModelInterface;

/**
 * @property-read bool $renderView Should render view
 * @property-read string $viewTemplate Should render view
 * @property-read bool $renderLayout Should render layout
 * @property-read string $layoutTemplateName Layout name
 * @property-read string $layoutTemplatePath Layout path
 * @property-read Model $viewModel Should render view
 */
class RenderOptionsContext extends ContextAbstract
{
    protected $contexts = array(
        'renderView' => true,
        'viewTemplate' => null,
        'renderLayout' => true,
        'layoutTemplateName' => '',
        'layoutTemplatePath' => '',
        'viewModel' => null,
    );

    protected $defaults = array();

    public function __construct(array $contexts = array())
    {
        parent::__construct($contexts);

        // Keep defaults for reset()
        $this->defaults = $this->contexts;
    }

    public function setRenderView($shouldRender = true)
    {
        $this->contexts['renderView'] = (bool) $shouldRender;

        return $this;
    }

    public function setRenderLayout($shouldRender = true)
    {
        $this->contexts['renderLayout'] = (bool) $shouldRender;

        return $this;
    }

    public function setTemplate($viewTemplate)
    {
        $this->contexts['viewTemplate'] = $viewTemplate;

        return $this;
    }

    public function setLayoutTemplate($layoutTemplateName, $layoutTemplatePath = null)
    {
        $this->contexts['layoutTemplateName'] = $layoutTemplateName;
        if ($layoutTemplatePath !== null) {
            $this->contexts['layoutTemplatePath'] = $layoutTemplatePath;
        }

        return $this;
    }

    public function setViewModel(Model $viewModel)
    {
        $this->contexts['viewModel'] = $viewModel;
        
        if ($viewModel instanceof ModelInterface) {
            $this->setTemplate($viewModel->getTemplate());
        }

        return $this;
    }

    public function reset()
    {
        $this->contexts = $this->defaults;
    }
}
