<?php

/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    class ScopeToFormImporter extends FormBuilder
    {
        function __construct(EntityProto $proto)
        {
            parent::__construct($proto);
        }

        /**
         * @return ObjectGetter
         **/
        protected function getGetter($object)
        {
            return new ScopeGetter($this->proto, $object);
        }

        /**
         * @return FormSetter
         **/
        protected function getSetter(&$object)
        {
            return new FormImporter($this->proto, $object);
        }
    }
}