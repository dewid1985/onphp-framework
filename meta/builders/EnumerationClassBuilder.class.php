<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
    final class EnumerationClassBuilder extends OnceBuilder
    {
        public static function build(MetaClass $class)
        {
            $out = self::getHead();

            if ($type = $class->getType()) {
                $type = "{$type->getName()} ";
            } else {
                $type = null;
            }

            $use = "use OnPhp\\Enumeration;";
            $out .= <<<EOT
namespace Business;

{$use}

{$type}class {$class->getName()} extends Enumeration
{
    // implement me!
}

EOT;

            return $out . self::getHeel();
        }
    }
}
?>