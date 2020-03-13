<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection\DocBlock\Tag;

use PHPUnit\Framework\TestCase;
use Zend\Code\Reflection\DocBlock\Tag\AuthorTag;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 */
class AuthorTagTest extends TestCase
{
    /**
     * @var AuthorTag
     */
    protected $tag;

    protected function setUp() : void
    {
        $this->tag = new AuthorTag();
    }

    public function testParseName()
    {
        $this->tag->initialize('Firstname Lastname');
        self::assertEquals('author', $this->tag->getName());
        self::assertEquals('Firstname Lastname', $this->tag->getAuthorName());
    }

    public function testParseNameAndEmail()
    {
        $this->tag->initialize('Firstname Lastname <test@domain.fr>');
        self::assertEquals('author', $this->tag->getName());
        self::assertEquals('Firstname Lastname', $this->tag->getAuthorName());
        self::assertEquals('test@domain.fr', $this->tag->getAuthorEmail());
    }
}
