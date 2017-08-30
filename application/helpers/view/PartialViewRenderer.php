<?php

/**
 * @author matthias.kerstner <matthias@kerstner.at> 
 */
class Zend_View_Helper_PartialViewRenderer extends Zend_View_Helper_Abstract {

    public $view;

    public function partialViewRenderer() {
        return $this;
    }

    public function setView(Zend_View_Interface $view) {
        $this->view = $view;
    }

    public function renderViewContent($scriptName, $data = array()) {
        $layoutHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Layout');
        $viewRendererHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

        $layoutHelper->disableLayout();
        $viewRendererHelper->setNoRender(TRUE);

        $this->view->data = array();

        foreach ($data as $k => $v) {
            $this->view->data[$k] = $v;
        }

        $content = $this->view->render($scriptName);

        $layoutHelper->enableLayout();
        $viewRendererHelper->setNoRender(FALSE);

        return $content;
    }

}

?>
