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
    final class DictionaryDaoBuilder extends BaseBuilder
    {
        public static function build(MetaClass $class)
        {
            $out = self::getHead();
            $use = "use ".ONPHP_NAMESPACE."\\StorableDAO;";
            
            $out .= <<<EOT
namespace Auto\\DAOs;

{$use}

abstract class Auto{$class->getName()}DAO extends StorableDAO
{

EOT;

            $pointers = self::buildPointers($class);

            $out .= <<<EOT
{$pointers}
}

EOT;

            return $out . self::getHeel();
        }
    }
}
?>