<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.09.17
 * Time: 14:16
 */

namespace OnPhp {
    /**
     * Class XmlAttribute
     * @package Point\Core\Xml
     */
    class XmlAttribute extends Xml
    {

        /** @var */
        protected $value;

        /**
         * @param $value
         * @return $this
         */
        public function setValue($value)
        {
            $this->value = $value;
            return $this;
        }

        /**
         * @return mixed
         */
        public function getValue()
        {
            return $this->value;
        }

        /**
         * @return string
         */
        public function toXml()
        {
            return $this->getName() . "=\"" . $this->value . "\"";
        }
    }
}