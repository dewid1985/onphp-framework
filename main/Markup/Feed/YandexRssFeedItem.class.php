<?php
/***************************************************************************
 *   Copyright (C) 2010 by Alexandr S. Krotov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Feed
     **/
    class YandexRssFeedItem extends FeedItem
    {
        private $fullText = null;
        private $genre = null;

        public function getFullText()
        {
            return $this->fullText;
        }

        /**
         * @return YandexRssFeedItem
         **/
        public function setFullText($fullText)
        {
            $this->fullText = $fullText;

            return $this;
        }

        /**
         * @return null
         */
        public function getGenre()
        {
            return $this->genre;
        }

        /**
         * @param $genre
         * @return $this
         */
        public function setGenre($genre)
        {
            $this->genre = $genre;

            return $this;
        }
    }
}
