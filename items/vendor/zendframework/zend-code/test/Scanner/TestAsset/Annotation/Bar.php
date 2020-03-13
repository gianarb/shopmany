<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Scanner\TestAsset\Annotation;

use Zend\Code\Annotation\AnnotationInterface;

class Bar implements AnnotationInterface
{
    protected $content = null;

    public function initialize($content)
    {
        $this->content = $content;
    }
}
