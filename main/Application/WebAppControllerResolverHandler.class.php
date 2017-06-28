<?php

/***************************************************************************
 *   Copyright (C) 2009 by Solomatin Alexandr                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPhp {
    class WebAppControllerResolverHandler implements InterceptingChainHandler
    {

        const
            CONTROLLER_POSTFIX = 'Controller',
            CONTROLLER_NAMESPACE = "Controllers" . '\\';

        protected $defaultController = 'HomeController';

        protected $notfoundController = 'NotFoundController';

        /**
         * @return WebAppControllerResolverHandler
         */
        public function run(InterceptingChain $chain)
        {
            if ($controllerName = $this->getControllerNameByArea($chain)) {
                $chain->setControllerName($controllerName);
            } elseif ($controllerName = $this->getControllerNameByOtherData($chain)) {
                $chain->setControllerName($controllerName);
            } else {
                $chain->setControllerName($this->defaultController);
            }
            $chain->next();

            return $this;
        }

        /**
         * @param InterceptingChain $chain
         * @return null|string
         */
        protected function getControllerNameByArea(InterceptingChain $chain)
        {
            /** @var HttpRequest $request */
            $request = $chain->getRequest();

            $area = null;

            if ($request->hasAttachedVar('area'))
                $area = $request->getAttachedVar('area');
            elseif ($request->hasGetVar('area'))
                $area = $request->getGetVar('area');
            elseif ($request->hasPostVar('area'))
                $area = $request->getPostVar('area');


            if ($request->hasAttachedVar('namespaces'))
                $area = $request->getAttachedVar('namespaces') . ucfirst($area);
            else
                $area = self::CONTROLLER_NAMESPACE . ucfirst($area);

            if (
                $area
                && $this->checkControllerName($area . self::CONTROLLER_POSTFIX, $chain->getPathController())
            ) {
                return $area . self::CONTROLLER_POSTFIX;
            } elseif ($area) {
                HeaderUtils::sendHttpStatus(new HttpStatus(HttpStatus::CODE_404));
                return self::CONTROLLER_NAMESPACE.$this->notfoundController;
            }

            return null;
        }

        protected function checkControllerName($controllerName, $path)
        {
            try {
                new  $controllerName;
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        protected function getControllerNameByOtherData(InterceptingChain $chain)
        {
            return null;
        }

        /**
         * @return WebAppControllerResolverHandler
         */
        public function setDefaultController($defaultController)
        {
            $this->defaultController = $defaultController;

            return $this;
        }

        /**
         * @return WebAppControllerResolverHandler
         */
        public function setNotfoundController($notfoundController)
        {
            $this->notfoundController = $notfoundController;

            return $this;
        }
    }
}