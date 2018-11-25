<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.09.17
 * Time: 14:01
 */

namespace OnPhp {
    class XmlChild extends Xml
    {
        /** @var null */
        protected $value = null;

        /**
         * @param $value
         * @return XmlChild
         */
        public function setValue($value): XmlChild
        {
            $this->value = $value;
            return $this;
        }

        /**
         * @param int $count
         * @return string
         */
        function toXml(int $count = 1)
        {
            if (is_string($this->value))
                if (trim($this->value) == '')
                    return null;

            return
                "<{$this->getName()}" .
                (
                $this->getAttributes() ?
                    ($this->toStringAttributes()) : null
                ) . (
                is_null($this->value) && !$this->getChildren() ? "/>" :
                    ">"
                    . (
                    $this->getChildren() ?
                        $this->toStringChildren($count + 1) : $this->value
                    )
                    . (
                    $this->getChildren() ? ($this->tabulation($count)) : null
                    )
                    . "</{$this->getName()}>");
        }
    }
}