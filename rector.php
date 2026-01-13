<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveParentDelegatingConstructorRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
                ->withPaths([
                    __DIR__ . '/lib',
                    __DIR__ . '/tests'
                ])
                ->withSkip([
                    __DIR__ . '/vendor',
                    //InlineClassRoutePrefixRector::class,
                    NewMethodCallWithoutParenthesesRector::class,
                    RemoveParentDelegatingConstructorRector::class,
                        //UnusedForeachValueToArrayKeysRector::class,
                        //RemoveUnusedForeachKeyRector::class,
                        //RemoveUselessParamTagRector::class,
                        //RemoveUselessReturnTagRector::class
                        //SimplifyUselessVariableRector::class
                ])
                ->withPreparedSets(
                //deadCode: true,
                //codeQuality: true,
                //codingStyle: true,
                //naming: true,
                //privatization: true,
                //typeDeclarations: true,
                //rectorPreset: true
                )
                ->withPhpSets(php84: true)
                ->withPhpVersion(PhpVersion::PHP_84)
                ->withAttributesSets(symfony: true, doctrine: true)
                ->withComposerBased(twig: true, doctrine: true, phpunit: true, symfony: true)
                ->withSets(
                        [
                            LevelSetList::UP_TO_PHP_84
                        ]
                )
                ->withRules(
                        [
                        //ExplicitNullableParamTypeRector::class,
                        //AddOverrideAttributeToOverriddenMethodsRector::class,
                        //ReturnTypeFromStrictNativeCallRector::class
                        ]
                )
                ->withTypeCoverageLevel(50)
                ->withDeadCodeLevel(50)
                ->withCodeQualityLevel(50)
                //->withCodingStyleLevel(24) // use php-csfix instead
;
