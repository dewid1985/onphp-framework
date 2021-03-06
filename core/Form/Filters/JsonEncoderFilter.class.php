<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Filters
     **/
    class JsonEncoderFilter extends BaseFilter
    {
        /**
         * @return JsonEncoderFilter
         **/
        public static function me()
        {
            return Singleton::getInstance(__CLASS__);
        }

        /**
         * @param $value
         * @return string
         */
        public function apply($value) : string
        {
            return json_encode($value);
        }
    }
}