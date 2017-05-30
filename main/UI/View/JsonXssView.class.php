<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Flow
     **/
    class JsonXssView implements View, Stringable
    {
        /**
         * Javascript valid function name pattern
         */
        const CALLBACK_PATTERN = '/^[\$A-Z_][0-9A-Z_\$\.]*$/i';

        /**
         * Default prefix
         * @var string
         */
        protected $prefix = 'window.';

        /**
         * Default callback
         * @var string
         */
        protected $variable = 'data';

        /**
         * @var int
         */
        protected $options = 0;

        /**
         * @param string $variable
         * @return $this
         */
        public function setVariable(string $variable)
        {
            $this->variable = $variable;
            return $this;
        }


        /**
         * @param $value
         * @return JsonXssView
         * @throws WrongArgumentException
         */
        public function setPrefix($value)
        {
            if (!preg_match(static::CALLBACK_PATTERN, $value)) {
                throw new WrongArgumentException('invalid prefix name, you should set valid javascript function name! gived "' . $value . '"');
            }

            $this->prefix = $value;

            return $this;
        }


        public function setHexQuot($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_QUOT;
            } else {
                $this->options = $this->options & ~JSON_HEX_QUOT;
            }

            return $this;
        }


        public function setHexTag($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_TAG;
            } else {
                $this->options = $this->options & ~JSON_HEX_TAG;
            }

            return $this;
        }


        public function setHexAmp($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_AMP;
            } else {
                $this->options = $this->options & ~JSON_HEX_AMP;
            }

            return $this;
        }


        public function setHexApos($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_HEX_APOS;
            } else {
                $this->options = $this->options & ~JSON_HEX_APOS;
            }

            return $this;
        }


        public function setForceObject($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_FORCE_OBJECT;
            } else {
                $this->options = $this->options & ~JSON_FORCE_OBJECT;
            }

            return $this;
        }


        public function setNumericCheck($flag = false)
        {
            if ($flag) {
                $this->options = $this->options | JSON_NUMERIC_CHECK;
            } else {
                $this->options = $this->options & ~JSON_NUMERIC_CHECK;
            }

            return $this;
        }


        public function setPrettyPrint($flag = false)
        {
            if (defined("JSON_PRETTY_PRINT")) {
                if ($flag) {
                    $this->options = $this->options | JSON_PRETTY_PRINT;
                } else {
                    $this->options = $this->options & ~JSON_PRETTY_PRINT;
                }
            }

            return $this;
        }


        public function setUnescapedSlashes($flag = false)
        {
            if (defined("JSON_UNESCAPED_SLASHES")) {
                if ($flag) {
                    $this->options = $this->options | JSON_UNESCAPED_SLASHES;
                } else {
                    $this->options = $this->options & ~JSON_UNESCAPED_SLASHES;
                }
            }

            return $this;
        }

        /**
         * @param Model $model
         * @return string
         */
        public function toString($model = null) : string
        {
            /*
             * Escaping warning datas
             */
            $this->setHexAmp(true);
            $this->setHexApos(true);
            $this->setHexQuot(true);
            $this->setHexTag(true);

            $json = $this->wrap($model);

            $json = str_ireplace(
                ['u0022', 'u0027'],
                ['\u0022', '\u0027'],
                $json
            );

            $result = "\t" . $this->prefix . $this->variable . '=\'' . $json . '\';' . "\n";
            return $result;
        }

        /**
         * @param Model $model
         * @return string
         */
        public function wrap($model = null) : string
        {
            Assert::isTrue($model === null || $model instanceof Model);
            if ($this->options) {
                return json_encode($model ? $model->getList() : [], $this->options);
            } else {
                return json_encode($model ? $model->getList() : []);
            }
        }

        /**
         * @param null $model
         * @return $this
         */
        public function render($model = null)
        {
            if (!headers_sent()) {
                header('Content-Type: ' . 'text/javascript');
            }

            echo $this->toString($model);

            return $this;
        }
    }
}