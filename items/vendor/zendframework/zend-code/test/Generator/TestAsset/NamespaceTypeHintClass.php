<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Namespaced\TypeHint {

    use OtherNamespace\ParameterClass;

    class Bar
    {

        public function method(ParameterClass $object)
        {
        }
    }
}

namespace OtherNamespace {

    class ParameterClass
    {

    }
}
