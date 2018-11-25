<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 28.09.17
 * Time: 15:10
 */

namespace OnPhp {
    abstract class XmlItem extends Xml implements IXml
    {
        /** @var string $itemName */
        protected $itemName;

        /**
         * @return string
         */
        public function getItemName()
        {
            return $this->itemName;
        }

        /**
         * @param $itemName
         * @return $this
         */
        public function setItemName($itemName)
        {
            $this->itemName = $itemName;
            return $this;
        }
    }
}