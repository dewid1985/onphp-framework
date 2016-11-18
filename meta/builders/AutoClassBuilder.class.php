<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Builders
     **/
    final class AutoClassBuilder extends BaseBuilder
    {
        public static function build(MetaClass $class)
        {
            $head = self::getHead();

            $head .= "namespace Auto\\Business;\n\n";
            $use = "";

            $classes = [];

            $out = "abstract class Auto{$class->getName()}";

            $classes[] = "Business\\" . $class->getName();
            $isNamed = false;

            if ($parent = $class->getParent()) {
                $classes[] = "{$parent->getName()}";
                $out .= " extends {$parent->getName()}";
            } elseif (
                $class->getPattern() instanceof DictionaryClassPattern
                && $class->hasProperty('name')
            ) {
                $classes[] = "OnPhp\\NamedObject";
                $out .= " extends NamedObject";
                $isNamed = true;
            } elseif (!$class->getPattern() instanceof ValueObjectPattern) {
                $classes[] = "OnPhp\\IdentifiableObject";
                $out .= " extends IdentifiableObject";
            }

            if ($interfaces = $class->getInterfaces()) {
                $out .= ' implements ' . implode(', ', $interfaces);
            }

            $out .= "\n{\n";

            foreach ($class->getProperties() as $property) {
                if (!self::doPropertyBuild($class, $property, $isNamed)) {
                    continue;
                }

                $out .=
                    "protected \${$property->getName()} = "
                    . "{$property->getType()->getDeclaration()};\n";

                if ($property->getFetchStrategyId() == FetchStrategy::LAZY) {
                    $out .=
                        "protected \${$property->getName()}Id = null;\n";
                }
            }

            $valueObjects = [];

            foreach ($class->getProperties() as $property) {
                if (
                    $property->getType() instanceof ObjectType
                    && !$property->getType()->isGeneric()
                    && $property->getType()->getClass()->getPattern()
                    instanceof ValueObjectPattern
                ) {
                    $valueObjects[$property->getName()] =
                        $property->getType()->getClassName();
                }
            }

            if ($valueObjects) {
                $out .= <<<EOT

public function __construct()
{

EOT;
                foreach ($valueObjects as $propertyName => $className) {
                    $out .= "    \$this->{$propertyName} = new {$className}();\n";
                }

                $out .= "}\n";
            }

            foreach ($class->getProperties() as $property) {

                if (!self::doPropertyBuild($class, $property, $isNamed)) {
                    continue;
                }

                if ($property->getType() instanceof ObjectType) {

                    $classType = $property->getType()->getClassName();

                    switch ($classType) {
                        case "TimestampTZ" :
                            if (!in_array("OnPhp\\" . $classType, $classes))
                                $classes[] = "OnPhp\\" . $classType;
                            break;
                        default:
                            if (!in_array("Business\\" . $classType, $classes))
                                $classes[] = "Business\\" . $classType;
                    }
                };

                $out .= $property->toMethods($class);
            }

            foreach ($classes as $names) {
                $use .= "use " . $names . ";\n";
            }

            $out .= "}\n";
            $out = $head . $use . "\n" . $out;
            $out .= self::getHeel();

            return $out;
        }

        private static function doPropertyBuild(
            MetaClass $class,
            MetaClassProperty $property,
            $isNamed
        )
        {
            if (
            $parentProperty =
                $class->isRedefinedProperty($property->getName())
            ) {
                // check wheter property fetch strategy becomes lazy
                if (
                    (
                        $parentProperty->getFetchStrategyId()
                        <> $property->getFetchStrategyId()
                    ) && (
                        $property->getFetchStrategyId() === FetchStrategy::LAZY
                    )
                ) {
                    return true;
                }

                return false;
            }

            if ($isNamed && $property->getName() == 'name') {
                return false;
            }

            if (
                ($property->getName() == 'id')
                && !$property->getClass()->getParent()
            ) {
                return false;
            }

            // do not redefine parent's properties
            if (
                $property->getClass()->getParent()
                && array_key_exists(
                    $property->getName(),
                    $property->getClass()->getAllParentsProperties()
                )
            ) {
                return false;
            }

            return true;
        }
    }
}
?>