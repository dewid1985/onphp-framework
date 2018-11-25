<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 21.09.17
 * Time: 13:53
 */

namespace OnPhp {

    abstract class Xml
    {
        /** @var null */
        private $name = null;

        /** @var XmlChild[] */
        private $children = [];

        /** @var XmlAttribute[] */
        private $attributes = [];

        /** @var  XmlChild */
        private $child;

        /**
         * Xml constructor.
         * @param string $name
         */
        public function __construct(string $name)
        {
            $this->name = $name;
        }

        /**
         * @return string|null
         */
        public function getName(): string
        {
            return $this->name;
        }

        /**
         * @return XmlChild[]
         */
        public function getChildren(): array
        {
            return $this->children;
        }

        /**
         * @return string
         */
        protected function toStringAttributes(): string
        {
            $string = "";
            foreach ($this->attributes as $attribute)
                $string .= " " . $attribute->toXml();
            return $string;
        }

        /**
         * @param int $count
         * @return string
         */
        protected function tabulation(int $count): string
        {
            $str = "";
            for ($i = 0; $i < $count; $i++)
                $str .= "    ";
            return $str;
        }

        /**
         * @param int $count
         * @return string
         */
        protected function toStringChildren(int $count = 1): string
        {
            $string = "";
            for ($i = 0; $i < count($this->children); $i++)
                if (count($this->children) > 1 && count($this->children) != ($i + 1)) {

                    if (is_null($this->children[$i]->toXml())) {
                        $string .= "\n";
                        continue;
                    }

                    if ($this->children[$i] instanceof Xml && $this->children[$i] instanceof IXml)
                        $string .= "\n" . $this->tabulation($count) .
                            $this->children[$i]->getXmlChild()->toXml($count);
                    else
                        $string .= "\n" . $this->tabulation($count) . $this->children[$i]->toXml($count);

                } else {

                    if (is_null($this->children[$i]->toXml())) {
                        $string .= "\n";
                        continue;
                    }

                    if ($this->children[$i] instanceof Xml && $this->children[$i] instanceof IXml)
                        $string .= "\n" . $this->tabulation($count) .
                            $this->children[$i]->getXmlChild()->toXml($count) . "\n";
                    else
                        $string .= "\n" . $this->tabulation($count) . $this->children[$i]->toXml($count) . "\n";
                }

            return $string;
        }

        /**
         * @return XmlChild
         * @throws \Exception
         */
        public function getXmlChild()
        {
            if (!$this instanceof IXml)
                throw new \Exception(get_class($this) . ' object not instance IXml');

            $this->child = new XmlChild($this->getName());

            if (!is_null($this->getAttributes()))
                if (is_array($this->getAttributes()))
                    foreach ($this->getAttributes() as $attr)
                        if ($attr instanceof XmlAttribute)
                            $this->child->setAttribute($attr);


            foreach (get_object_vars($this) as $key => $value) {
                if (
                    $key === 'children' || $key === 'name' ||
                    $key === 'attributes' || $key === 'child' || $key === 'value'
                )
                    continue;

                if ($value instanceof XmlChild) {
                    $this->child->setChild($value);
                    continue;
                }
                $this->child->setChild((new XmlChild($key))->setValue($value));
            }
            return $this->child;
        }

        /**
         * @param XmlChild $child
         * @return $this
         */
        public function setChild(XmlChild $child)
        {
            array_push($this->children, $child);
            return $this;
        }

        /**
         * @param XmlAttribute $attribute
         * @return $this
         */
        public function setAttribute(XmlAttribute $attribute)
        {
            $this->attributes[] = $attribute;
            return $this;
        }

        /**
         * @return XmlAttribute[]
         */
        public function getAttributes()
        {
            return $this->attributes;
        }
    }
}