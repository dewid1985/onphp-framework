<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 06.12.17
 * Time: 21:01
 */

namespace OnPhp {

    class XmlDocumentType
    {
        /** @var string */
        private $docType = "";
        /** @var string */
        private $system = "";

        /**
         * @param string $docType
         * @return XmlDocumentType
         */
        public function setDocType(string $docType): XmlDocumentType
        {
            $this->docType = $docType;
            return $this;
        }

        /**
         * @return string
         */
        public function getDocType()
        {
            return $this->docType;
        }

        /**
         * @param string $system
         * @return XmlDocumentType
         */
        public function setSystem(string $system): XmlDocumentType
        {
            $this->system = $system;
            return $this;
        }

        public function toString()
        {
            return "<!DOCTYPE " . $this->docType . " SYSTEM \"" . $this->system . "\">\n";
        }
    }

}