<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * @ingroup Projections
     **/
    class ClassProjection implements ObjectProjection
    {
        protected $className = null;

        /**
         * ClassProjection constructor.
         * @param $class
         */
        public function __construct($class)
        {
            Assert::isTrue(
                ClassUtils::isInstanceOf($class, 'Prototyped')
            );

            if (is_object($class))
                $this->className = get_class($class);
            else
                $this->className = $class;
        }

        /**
         * @return JoinCapableQuery
         **/
        public function process(Criteria $criteria, JoinCapableQuery $query)
        {
            $dao = call_user_func([$this->className, 'dao']);

            foreach ($dao->getFields() as $field)
                $this->subProcess(
                    $query,
                    new DBField($field, $dao->getTable())
                );

            return $query;
        }

        /* void */
        protected function subProcess(
            JoinCapableQuery $query, DBField $field
        )
        {
            $query->get($field);
        }
    }
}