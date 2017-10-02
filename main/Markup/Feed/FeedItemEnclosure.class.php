<?php


namespace OnPhp {
    class FeedItemEnclosure
    {
        private $url = null;
        private $length = null;
        private $type = null;

        /**
         * @return null
         */
        public function getUrl()
        {
            return $this->url;
        }

        /**
         * @param $url
         * @return $this
         */
        public function setUrl($url)
        {
            $this->url = $url;

            return $this;
        }

        /**
         * @return null
         */
        public function getLength()
        {
            return $this->length;
        }

        /**
         * @param $length
         * @return $this
         */
        public function setLength($length)
        {
            $this->length = $length;

            return $this;
        }

        /**
         * @return null
         */
        public function getType()
        {
            return $this->type;
        }

        /**
         * @param $type
         * @return $this
         */
        public function setType($type)
        {
            $this->type = $type;

            return $this;
        }


    }
}