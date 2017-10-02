<?php


namespace OnPhp {
    class YandexRssFeedChannel extends FeedChannel
    {
        private $logo;
        private $logoSquare;
        private $analytics;

        /**
         * @return mixed
         */
        public function getLogo()
        {
            return $this->logo;
        }

        /**
         * @param $logo
         * @return $this
         */
        public function setLogo($logo)
        {
            $this->logo = $logo;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLogoSquare()
        {
            return $this->logoSquare;
        }

        /**
         * @param $logoSquare
         * @return $this
         */
        public function setLogoSquare($logoSquare)
        {
            $this->logoSquare = $logoSquare;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getAnalytics()
        {
            return $this->analytics;
        }

        /**
         * @param $analytics
         * @return $this
         */
        public function setAnalytics($analytics)
        {
            $this->analytics = $analytics;

            return $this;
        }


    }
}