<?php
declare(strict_types=1);

namespace Tests\EoneoPay\Utils;

use EoneoPay\Utils\AnnotationReader;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\AnnotationReaderParentStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\AnnotationReaderStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\AnnotationReaderWithMethodStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\Annotations\MethodAnnotationStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\Annotations\TestAnnotationStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\Annotations\TestMultipleAnnotationsStub;
use Tests\EoneoPay\Utils\Stubs\AnnotationReader\Annotations\UnusedAnnotationStub;

/**
 * @covers \EoneoPay\Utils\AnnotationReader
 */
class AnnotationReaderTest extends TestCase
{
    /**
     * Ensure exception isn't thrown if an invalid class is specified
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException If opcache is disabled
     */
    public function testAnnontationReadingInvalidPropertyOrClassDoesNotThrowException(): void
    {
        self::assertEmpty((new AnnotationReader())->getClassPropertyAnnotations('blah', ['test']));
    }

    /**
     * Test the annotation reader resolving a method will respect the desired annotation
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testAnnotationReaderResolvesMethodAnnotation(): void
    {
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getClassMethodAnnotations(
            AnnotationReaderWithMethodStub::class,
            'aPublicMethod',
            MethodAnnotationStub::class
        );

        self::assertEquals([
            new MethodAnnotationStub(['value' => 'yes']),
            new MethodAnnotationStub(['value' => 'maybe'])
        ], $annotation);
    }

    /**
     * Test the annotation reader will return null if annotation class does not exist against method
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testAnnotationReaderResolvesNullIfNoAnnotation(): void
    {
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getClassMethodAnnotations(
            AnnotationReaderWithMethodStub::class,
            'aPublicMethod',
            'AnInvalidClass'
        );

        self::assertEmpty($annotation);
    }

    /**
     * Test the annotation reader resolving will return null if the class does not exist
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException
     */
    public function testAnnotationReaderReturnsNullForIncorrectClass(): void
    {
        $annotationReader = new AnnotationReader();
        $annotation = $annotationReader->getClassMethodAnnotations(
            '\SomeClassThatDoesNotExist',
            'aPublicMethod',
            MethodAnnotationStub::class
        );

        self::assertEmpty($annotation);
    }

    /**
     * Ensure annotations can be fetched from a class which uses them
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException If opcache is disabled
     */
    public function testAnnotationsCanBeReadFromClass(): void
    {
        $annotations = (new AnnotationReader())->getClassPropertyAnnotation(
            AnnotationReaderParentStub::class,
            TestAnnotationStub::class
        );

        self::assertArrayHasKey('parent', $annotations);
        self::assertObjectHasAttribute('name', $annotations['parent']);
        self::assertObjectHasAttribute('enabled', $annotations['parent']);
        self::assertSame('parent_property', $annotations['parent']->name);
        self::assertTrue($annotations['parent']->enabled);
    }

    /**
     * Ensure annotations can be read recursively through parents and traits
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException If opcache is disabled
     */
    public function testAnnotationsCanBeReadRecursivelyFromClass(): void
    {
        $annotations = (new AnnotationReader())->getClassPropertyAnnotation(
            AnnotationReaderStub::class,
            TestAnnotationStub::class
        );

        self::assertArrayHasKey('baseProperty', $annotations);
        self::assertArrayHasKey('parent', $annotations);
        self::assertArrayHasKey('trait', $annotations);
        self::assertArrayHasKey('parentTrait', $annotations);
        self::assertArrayNotHasKey('noAnnotation', $annotations);
    }

    /**
     * Ensure multiple annotations can be read recursively through parents and traits
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException If opcache is disabled
     */
    public function testMultipleAnnotationsCanBeReadRecursivelyFromClass(): void
    {
        $annotations = (new AnnotationReader())->getClassPropertyAnnotations(
            AnnotationReaderStub::class,
            [TestAnnotationStub::class, TestMultipleAnnotationsStub::class]
        );

        $tests = [
            'baseProperty' => 2,
            'parent' => 4,
            'trait' => 2,
            'parentTrait' => 3
        ];

        foreach ($tests as $key => $count) {
            self::assertArrayHasKey($key, $annotations);
            self::assertCount($count, $annotations[$key]);
        }

        self::assertArrayNotHasKey('noAnnotation', $annotations);
    }

    /**
     * Ensure unused annotations return empty array
     *
     * @return void
     *
     * @throws \EoneoPay\Utils\Exceptions\AnnotationCacheException If opcache is disabled
     */
    public function testUnusedAnnotationReturnsEmptyArray(): void
    {
        $annotations = (new AnnotationReader())->getClassPropertyAnnotation(
            AnnotationReaderStub::class,
            UnusedAnnotationStub::class
        );

        self::assertEmpty($annotations);
    }
}
