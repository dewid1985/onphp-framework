<?php

/***************************************************************************
 *   Copyright (C) 2008 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    class RouterHostnameRule extends RouterBaseRule
    {
        const SCHEME_HTTP = 'http';
        const SCHEME_HTTPS = 'https';

        protected $hostVariable = ':';
        protected $regexDelimiter = '#';

        protected $scheme = null;
        protected $defaultRegex = null;
        protected $route = null;
        protected $routeProcessed = false;
        protected $variables = [];
        protected $parts = [];
        protected $requirements = [];
        protected $values = [];

        protected $staticCount = 0;

        public function __construct($route)
        {
            $this->route = trim($route, '.');
            $this->scheme = self::SCHEME_HTTP;
        }

        /**
         * @return array
         */
        public function getRequirements() : array
        {
            return $this->requirements;
        }

        /**
         * @return RouterHostnameRule
         **/
        public function setRequirements(array $reqirements)
        {
            $this->requirements = $reqirements;

            return $this;
        }

        /**
         * @return RouterHostnameRule
         **/
        public function setSecure()
        {
            $this->scheme = self::SCHEME_HTTPS;

            return $this;
        }

        /**
         * @return null|string
         */
        public function getScheme()
        {
            return $this->scheme;
        }

        /**
         * @return RouterHostnameRule
         **/
        public function setScheme($schema)
        {
            $this->scheme = $schema;

            return $this;
        }

        /**
         * @param HttpRequest $request
         * @return array
         * @throws RouterException
         */
        public function match(HttpRequest $request)
        {
            $this->processRoute();

            if (
                $this->isSecureRequest($request)
                && !$this->isSecure()
            ) {
                return [];
            }

            if ($request->hasServerVar('HTTP_HOST')) {
                $host = $request->getServerVar('HTTP_HOST');
            } else {
                throw new RouterException('Can not find host');
            }

            $result = [];

            if (preg_match('#:\d+$#', $host, $result) === 1) {
                $host = substr($host, 0, -strlen($result[0]));
            }

            $hostStaticCount = 0;
            $values = [];

            $host = trim($host, '.');

            // FIXME: strpos('.', ...), probably?
            if ($host) {
                $host = explode('.', $host);

                foreach ($host as $pos => $hostPart) {
                    if (!array_key_exists($pos, $this->parts)) {
                        return [];
                    }

                    $name =
                        isset($this->variables[$pos])
                            ? $this->variables[$pos]
                            : null;

                    $hostPart = urldecode($hostPart);

                    if (
                        ($name === null)
                        && ($this->parts[$pos] != $hostPart)
                    ) {
                        return [];
                    }

                    if (
                        ($this->parts[$pos] !== null)
                        && !preg_match(
                            $this->regexDelimiter
                            . '^' . $this->parts[$pos] . '$'
                            . $this->regexDelimiter . 'iu',

                            $hostPart
                        )
                    ) {
                        return [];
                    }

                    if ($name !== null) {
                        $values[$name] = $hostPart;
                    } else {
                        ++$hostStaticCount;
                    }
                }
            }

            if ($this->staticCount != $hostStaticCount) {
                return [];
            }

            $return = $values + $this->defaults;

            foreach ($this->variables as $var) {
                if (!array_key_exists($var, $return)) {
                    return [];
                }
            }

            $this->values = $values;

            return $return;
        }

        /**
         * @return RouterHostnameRule
         **/
        protected function processRoute()
        {
            if ($this->routeProcessed) {
                return $this;
            }

            // FIXME: if (strpos('.', ...), probably?
            if ($this->route) {
                foreach (explode('.', $this->route) as $pos => $part) {
                    if (substr($part, 0, 1) == $this->hostVariable) {
                        $name = substr($part, 1);

                        $this->parts[$pos] = (
                        isset($this->requirements[$name])
                            ? $this->requirements[$name]
                            : $this->defaultRegex
                        );

                        $this->variables[$pos] = $name;
                    } else {
                        $this->parts[$pos] = $part;
                        ++$this->staticCount;
                    }
                }
            }

            $this->routeProcessed = true;

            return $this;
        }

        /**
         * @param HttpRequest $request
         * @return bool
         */
        protected function isSecureRequest(HttpRequest $request) : bool
        {
            return (
                $request->hasServerVar('HTTPS')
                && $request->getServerVar('HTTPS') == 'on'
            ) || (
                $request->hasServerVar('SERVER_PORT')
                && (int)$request->getServerVar('SERVER_PORT') === 443
            );
        }

        /**
         * @return bool
         */
        public function isSecure() : bool
        {
            return $this->scheme == self::SCHEME_HTTPS;
        }

        /**
         * @param array $data
         * @param bool|false $reset
         * @param bool|false $encode
         * @return string
         * @throws RouterException
         */
        public function assembly(
            array $data = [],
            $reset = false,
            $encode = false
        )
        {
            $this->processRoute();

            $host = [];
            $flag = false;

            foreach ($this->parts as $key => $part) {
                $name =
                    isset($this->variables[$key])
                        ? $this->variables[$key]
                        : null;

                $useDefault = false;

                if (
                    isset($name)
                    && array_key_exists($name, $data)
                    && ($data[$name] === null)
                ) {
                    $useDefault = true;
                }

                if ($name) {
                    if (isset($data[$name]) && !$useDefault) {
                        $host[$key] = $data[$name];
                        unset($data[$name]);
                    } elseif (
                        !$reset
                        && !$useDefault
                        && isset($this->values[$name])
                    ) {
                        $host[$key] = $this->values[$name];
                    } elseif (isset($this->defaults[$name])) {
                        $host[$key] = $this->defaults[$name];
                    } else {
                        // FIXME: bogus message
                        throw new RouterException($name . ' is not specified');
                    }
                } else {
                    $host[$key] = $part;
                }
            }

            $return = null;

            foreach (array_reverse($host, true) as $key => $value) {
                if (
                    $flag
                    || !isset($this->variables[$key])
                    || ($value !== $this->getDefault($this->variables[$key]))
                ) {
                    if ($encode) {
                        $value = urlencode($value);
                    }

                    $return = '.' . $value . $return;
                    $flag = true;
                }
            }

            // FIXME: rtrim, probably?
            $host = trim($return, '.');

            return $this->resolveSchema() . '://' . $host . $this->resolvePath();

        }

        /**
         * @return null|string
         * @throws RouterException
         */
        protected function resolveSchema()
        {
            $base = RouterRewrite::me()->getBaseUrl();

            if ($this->scheme) {
                return $this->scheme;
            } elseif (
                ($base instanceof HttpUrl)
                && $base->getScheme()
            ) {
                return $base->getScheme();
            } else {
                throw new RouterException('Cannot resolve scheme');
            }
        }

        /**
         * @return string
         */
        protected function resolvePath()
        {
            if (
                ($base = RouterRewrite::me()->getBaseUrl())
                && ($this->scheme == $base->getScheme())
            ) {
                return rtrim($base->getPath(), '/');
            } else {
                return '';
            }
        }
    }
}

