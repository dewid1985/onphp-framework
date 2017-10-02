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
     * @ingroup Feed
     **/
    class YandexRssChannelWorker extends Singleton implements FeedChannelWorker
    {
        /**
         * @return RssChannelWorker
         **/
        public static function me()
        {
            return Singleton::getInstance(__CLASS__);
        }

        /**
         * @return FeedChannel
         **/
        public function makeChannel(\SimpleXMLElement $xmlFeed)
        {
            if (
                (!isset($xmlFeed->channel))
                || (!isset($xmlFeed->channel->title))
            ) {
                throw new WrongStateException(
                    'there are no channels in given rss'
                );
            }

            $feedChannel =
                new FeedChannel((string)$xmlFeed->channel->title);

            if (isset($xmlFeed->channel->link)) {
                $feedChannel->setLink((string)$xmlFeed->channel->link);
            }

            return $feedChannel;
        }

        public function toXml($channel, $itemsXml)
        {
            return
                '<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/" xmlns:yandex="http://news.yandex.ru" version="' . RssFeedFormat::VERSION . '">'
                . '<channel>'
                . '<title>' . $channel->getTitle() . '</title>'
                . (
                $channel->getLink()
                    ? '<link>' . $channel->getLink() . '</link>'
                    : null
                )
                . (
                $channel->getLogo()
                    ? '<yandex:logo>'.$channel->getLogo().'</yandex:logo>'
                    : null
                ) . (
                $channel->getLogoSquare()
                    ? '<yandex:logo type="square">'.$channel->getLogoSquare().'</yandex:logo>'
                    : null
                ) . (
                $channel->getDescription()
                    ?
                    '<description>'
                    . $channel->getDescription()
                    . '</description>'
                    : null
                ) . (
                $channel->getLanguage()
                    ?
                    '<language>'
                    . $channel->getLanguage()
                    . '</language>'
                    : null
                ) . (
                $channel->getAnalytics()
                    ?
                    '<yandex:analytics type="' . $channel->getAnalytics() . '"/>'
                    : null
                ) . (
                $channel->getLastBuildDate()
                    ?
                    '<lastBuildDate>'
                    . date('r', $channel->getLastBuildDate()->toStamp())
                    . '</lastBuildDate>'
                    : null
                )
                . $itemsXml
                . '</channel>'
                . '</rss>';
        }
    }
}