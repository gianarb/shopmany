<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Code\Reflection;

use PHPUnit\Framework\TestCase;
use Zend\Code\Generator\DocBlock\Tag\VarTag;
use Zend\Code\Reflection;

/**
 * @group      Zend_Reflection
 * @group      Zend_Reflection_DocBlock
 * @group      Zend_Reflection_DocBlock_Tag
 */
class ReflectionDocBlockTagTest extends TestCase
{
    public function testTagDescriptionIsReturned()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $authorTag = $classReflection->getDocBlock()->getTag('author');
        self::assertEquals('Ralph Schindler', $authorTag->getAuthorName());
        self::assertEquals('ralph.schindler@zend.com', $authorTag->getAuthorEmail());
    }

    public function testTagShouldAllowJustTagNameInDocBlockTagLine()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('emptyTag');
        self::assertEquals('emptyTag', $tag->getName(), 'Factory First Match Failed');
    }

    public function testTagShouldAllowMultipleWhitespacesBeforeDescription()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('descriptionTag');
        self::assertNotEquals(
            '          A tag with just a description',
            $tag->getContent(),
            'Final Match Failed'
        );
        self::assertEquals(
            'A tag with just a description',
            $tag->getContent(),
            'Final Match Failed'
        );
    }

    public function testToString()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $tag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('descriptionTag');

        $expectedString = 'DocBlock Tag [ * @descriptionTag ]' . "\n";

        self::assertEquals($expectedString, (string) $tag);
    }

    public function testTypeParam()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');

        self::assertEquals('int', $paramTag->getType());
    }

    public function testVariableName()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');
        self::assertEquals('$one', $paramTag->getVariableName());
    }

    public function testAllowsMultipleSpacesInDocBlockTagLine()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');

        self::assertEquals('int', $paramTag->getType(), 'Second Match Failed');
        self::assertEquals('$var', $paramTag->getVariableName(), 'Third Match Failed');
        self::assertEquals(
            'Description of $var',
            $paramTag->getDescription(),
            'Final Match Failed'
        );
    }

    /**
     * @group ZF-8307
     */
    public function testNamespaceInParam()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass7::class);
        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('param');

        self::assertEquals('Zend\Foo\Bar', $paramTag->getType());
        self::assertEquals('$var', $paramTag->getVariableName());
        self::assertEquals('desc', $paramTag->getDescription());
    }

    public function testType()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass5::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');
        self::assertEquals('mixed', $paramTag->getType());
    }

    public function testAllowsMultipleSpacesInDocBlockTagLine2()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass6::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');

        self::assertEquals('string', $paramTag->getType(), 'Second Match Failed');
        self::assertEquals(
            'Description of return value',
            $paramTag->getDescription(),
            'Final Match Failed'
        );
    }

    /**
     * @group ZF-8307
     */
    public function testReturnClassWithNamespace()
    {
        $classReflection = new Reflection\ClassReflection(TestAsset\TestSampleClass7::class);

        $paramTag = $classReflection->getMethod('doSomething')->getDocBlock()->getTag('return');

        self::assertEquals('Zend\Code\Reflection\DocBlock', $paramTag->getType());
    }

    /**
     * @dataProvider propertyVarDocProvider
     */
    public function testPropertyVarDoc(
        string $property,
        array $expectedTypes,
        ?string $expectedName,
        ?string $expectedDescription
    ) {
        $classReflection = new Reflection\ClassReflection(
            TestAsset\TestSampleClass14::class
        );

        /** @var VarTag $varTag */
        $varTag = $classReflection
            ->getProperty($property)
            ->getDocBlock()
            ->getTag('var');

        self::assertSame($expectedTypes, $varTag->getTypes());
        self::assertSame($expectedName, $varTag->getVariableName());
        self::assertSame($expectedDescription, $varTag->getDescription());
    }

    public function propertyVarDocProvider(): array
    {
        return [
            'only type' => ['onlyType', ['string'], null, null],
            'type and description' => [
                'typeDescription',
                ['string'],
                null,
                'Foo bar',
            ],
            'type and name' => ['typeName', ['string'], '$typeName', null],
            'type, name and description' => [
                'typeNameDescription',
                ['string'],
                '$typeNameDescription',
                'Foo bar',
            ],
        ];
    }
}
