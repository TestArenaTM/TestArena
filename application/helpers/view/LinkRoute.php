<?php

/**
 * Description of Custom_View_Helper_LinkRoute
 *
 * @author matthias.kerstner
 */
class Zend_View_Helper_LinkRoute extends Zend_View_Helper_Abstract {

    public function linkRoute() {
        return $this;
    }

    /**
     *
     * @param type $route
     * @param boolean $full
     * @param type $options
     * @param type $useClassicGetParams
     * @param type $matchOptionsToRoute
     * @param array? $classicGetParams
     * @return string
     */
    public function getUrl($route, $full = false, $options = array(), $useClassicGetParams = false, $matchOptionsToRoute = false, $classicGetParams = null) {

        if (is_array($full)) {
            $options = $full;
            $full = false;
        }

        $urlHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('Url');
        $session = new Zend_Session_Namespace('visitor');
        $params = array('language' => isset($options['language']) ? $options['language'] : ($session->language != '' ? $session->language : 'en'));
        $paramsSuffix = '';

        if ($matchOptionsToRoute) {
            //use language set by multilanguage plugin
            $params = array_merge($params, $options);
        } else {
            if ($useClassicGetParams) {
                $paramsSuffix = '?';
                $first = true;
                foreach ($options as $k => $v) {
                    $paramsSuffix .= (($first) ? '' : '&') . $k . '=' . $v;
                    $first = false;
                }
            } else {
                foreach ($options as $k => $v) {
                    $paramsSuffix .= '/' . $k . '/' . $v;
                }
            }
        }

        $host = '';
        $url = '';

        if ($full) {
            $host = $this->getCurrentUrl(true);
        }

        try {

            $url = $urlHelper->url($params, $route);

            if (is_array($classicGetParams) && count($classicGetParams) > 0) { //append GET params
                $first = (mb_strpos('?', $paramsSuffix) === false);
                $classicGetParamsSuffix = '';

                foreach ($classicGetParams as $k => $v) {
                    $classicGetParamsSuffix .= (($first) ? '?' : '&') . $k . '=' . $v;
                    $first = false;
                }

                $paramsSuffix .= $classicGetParamsSuffix;
            }

            return ($host . $url . $paramsSuffix);
        } catch (Exception $e) {
          Zend_Registry::get('Zend_Log')->log($e->getMessage(), Zend_Log::ERR);
          throw new Exception('Failed to calculate route-URL');
        }
    }

    /**
     *
     * @param string $anchor
     * @param string $label
     * @return string 
     */
    public function linkByUrl($anchor, $label = '', $full = false, $attributes = array()) {
        $view = Zend_Layout::getMvcInstance()->getView();

        $attrStr = '';
        if (count($attributes)) {
            foreach ($attributes as $attrName => $attrVal)
                $attrStr .= ' ' . $attrName . '="' . htmlentities($attrVal) . '"';
        }

        return '<a href="' . $anchor . '"' . $attrStr . '>'
                . ($label != '' ? $view->translate()->translate($label) : $anchor) . '</a>';
    }

    /**
     *
     * @param string $route
     * @param string $label
     * @return string 
     */
    public function linkByRoute($route, $label = '', $full = false, $attr = array()) {
        return $this->linkByUrl($this->getUrl($route, $full), $label, $full, $attr);
    }

    /**
     * Returns URL of current request.
     * @return string
     */
    public function getCurrentUrl($hostOnly = false) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $host = '';

        if (!$request) { //fallback for cmdline calls (testing)
            $host = Zend_Registry::get('Zend_Config')->baseUrl;
        } else {
            $host = $request->getScheme() . '://' . $request->getHttpHost();
        }

        if ($hostOnly) {
            return $host;
        }

        return ($host . $_SERVER['REQUEST_URI']);
    }

    /**
     * Checks if current request is secure, i.e. uses httpS.
     * @return bool
     */
    public function isSecureRequest() {
        return (Zend_Controller_Front::getInstance()->getRequest()->getScheme() === 'https');
    }

}

?>
