<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Generator\DocBlock\Tag;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\TagManager;
use Zend\Code\Reflection\DocBlockReflection;

/**
 * @group Zend_Code_Generator
 * @group Zend_Code_Generator_Php
 */
class ParamTagTest extends TestCase
{
    /**
     * @var ParamTag
     */
    protected $tag;

    /**
     * @var TagManager
     */
    protected $tagmanager;

    protected function setUp() : void
    {
        $this->tag = new ParamTag();
        $this->tagmanager = new TagManager();
        $this->tagmanager->initializeDefaultTags();
    }

    protected function tearDown() : void
    {
        $this->tag = null;
        $this->tagmanager = null;
    }

    public function testGetterAndSetterPersistValue()
    {
        $this->tag->setVariableName('Foo');
        self::assertEquals('Foo', $this->tag->getVariableName());
    }

    public function testGetterForVariableNameTrimsCorrectly()
    {
        $this->tag->setVariableName('$param$');
        self::assertEquals('param$', $this->tag->getVariableName());
    }

    public function testNameIsCorrect()
    {
        self::assertEquals('param', $this->tag->getName());
    }

    public function testParamProducesCorrectDocBlockLine()
    {
        $this->tag->setVariableName('foo');
        $this->tag->setTypes('string|null');
        $this->tag->setDescription('description');
        self::assertEquals('@param string|null $foo description', $this->tag->generate());
    }

    public function testConstructorWithOptions()
    {
        $this->tag->setOptions([
            'variableName' => 'foo',
            'types' => ['string'],
            'description' => 'description',
        ]);
        $tagWithOptionsFromConstructor = new ParamTag('foo', ['string'], 'description');
        self::assertEquals($this->tag->generate(), $tagWithOptionsFromConstructor->generate());
    }

    public function testCreatingTagFromReflection()
    {
        $docreflection = new DocBlockReflection('/** @param int $foo description');
        $reflectionTag = $docreflection->getTag('param');

        /** @var ParamTag $tag */
        $tag = $this->tagmanager->createTagFromReflection($reflectionTag);
        self::assertInstanceOf(ParamTag::class, $tag);
        self::assertEquals('foo', $tag->getVariableName());
        self::assertEquals('description', $tag->getDescription());
        self::assertEquals('int', $tag->getTypesAsString());
    }
}
