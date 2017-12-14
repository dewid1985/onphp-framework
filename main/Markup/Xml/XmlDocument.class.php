<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.09.17
 * Time: 14:20
 */

namespace OnPhp {
    /**
     * Class XmlDocument
     * @package Point\Core\Xml
     */
    class XmlDocument extends Xml
    {

        /** @var string */
        protected $version = "1.0";

        /** @var string */
        protected $encoding = "UTF-8";

        protected $errors;

        protected $xsdFile = null;

        /** @var XmlDocumentType|null */
        protected $docType = null;

        /**
         * @param string $version
         * @return XmlDocument
         */
        public function setVersion(string $version): XmlDocument
        {
            $this->version = $version;
            return $this;
        }

        /**
         * @param XmlDocumentType $docType
         * @return XmlDocument
         */
        public function setDocType(XmlDocumentType $docType): XmlDocument
        {
            $this->docType = $docType;
            return $this;
        }

        /**
         * @param string $encoding
         * @return XmlDocument
         */
        public function setEncoding(string $encoding): XmlDocument
        {
            $this->encoding = $encoding;
            return $this;
        }

        /**
         * @param $xsdFile
         * @return XmlDocument
         */
        public function setXsdFile($xsdFile): XmlDocument
        {
            $this->xsdFile = $xsdFile;
            return $this;
        }

        /**
         * @return string
         */
        public function toXml()
        {
            return
                "<?xml version=\"{$this->version}\"" .
                (
                    $this->encoding ?
                        " encoding=\"" . $this->encoding . "\"" : null
                ) . "?>\n" .
                (
                $this->docType instanceof XmlDocumentType ?
                    $this->docType->toString() : null

                ) .
                (
                "<{$this->getName()}"
                ) .
                (
                $this->getAttributes() ?
                    ($this->toStringAttributes()) : null
                )
                . ">" .
                (
                $this->getChildren() ?
                    ($this->toStringChildren()) : null
                )
                . "</{$this->getName()}>";
        }

        /**
         * @return mixed
         */
        public function getErrors()
        {
            return $this->errors;
        }

        /**
         * @return bool
         */
        public function isValidate()
        {

            if (is_null($this->xsdFile))
                return true;

            $xml = new \DOMDocument($this->version, $this->encoding);

            $xml->loadXML(iconv("utf-8", $this->encoding, $this->toXml()));

            libxml_use_internal_errors(true);

            if (!$xml->schemaValidate($this->xsdFile)) {
                $this->errors = libxml_get_errors();
                libxml_clear_errors();
                return false;
            }

            return true;
        }
    }
}