<?php
/***************************************************************************
 *   Copyright (C) 2007 by Dmitry A. Lomash, Dmitry E. Demidov             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Class FeedChannel
     * @ingroup Feed
     * @package OnPhp
     */
    class FeedChannel
    {
        private $title = null;
        private $link = null;
        private $description = null;
        private $feedItems = [];
        private $lastBuildDate = null;
        private $language = null;

        public function __construct($title)
        {
            $this->title = $title;
        }


        public function getTitle()
        {
            return $this->title;
        }

        /**
         * @return FeedChannel
         **/
        public function setTitle($title)
        {
            $this->title = $title;

            return $this;
        }

        public function getDescription()
        {
            return $this->description;
        }

        /**
         * @return FeedChannel
         **/
        public function setDescription($description)
        {
            $this->description = $description;

            return $this;
        }

        public function getLink()
        {
            return $this->link;
        }

        /**
         * @return FeedChannel
         **/
        public function setLink($link)
        {
            $this->link = $link;

            return $this;
        }

        public function getFeedItems()
        {
            return $this->feedItems;
        }

        /**
         * @return Timestamp
         */
        public function getLastBuildDate()
        {
            return $this->lastBuildDate;
        }

        /**
         * @param Timestamp $lastBuildDate
         * @return $this
         */
        public function setLastBuildDate(Timestamp $lastBuildDate)
        {
            $this->lastBuildDate = $lastBuildDate;
            return $this;
        }


        /**
         * @return FeedChannel
         **/
        public function setFeedItems($feedItems)
        {
            $this->feedItems = $feedItems;

            return $this;
        }

        /**
         * @return null
         */
        public function getLanguage()
        {
            return $this->language;
        }

        /**
         * @return $this
         */
        public function setLanguage($language)
        {
            $this->language = $language;

            return $this;
        }

        /**
         * @return FeedChannel
         **/
        public function addFeedItem(FeedItem $feedItem)
        {
            $this->feedItems[] = $feedItem;

            return $this;
        }
    }
}