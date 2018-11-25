<?php
/***************************************************************************
 *   Copyright (C) 2005-2009 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Cacheless DAO worker.
 *
 * @see CommonDaoWorker for manual-caching one.
 * @see SmartDaoWorker for transparent one.
 *
 * @ingroup DAOs
 **/
namespace OnPhp {
    class NullDaoWorker extends CommonDaoWorker
    {
        /// single object getters
        //@{
        public function getById($id)
        {
            return parent::getById($id, $expires = Cache::DO_NOT_CACHE);
        }

        public function getByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getByLogic($logic, Cache::DO_NOT_CACHE);
        }

        public function getByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getByQuery($query, $expires);
        }

        public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getCustom($query, $expires);
        }
        //@}

        /// object's list getters
        //@{
        public function getListByIds(
            array $ids,
            $expires = Cache::EXPIRES_MEDIUM
        )
        {
            try {
                return
                    $this->getListByLogic(
                        Expression::in(
                            new DBField(
                                $this->dao->getIdName(),
                                $this->dao->getTable()
                            ),
                            $ids
                        )
                    );
            } catch (ObjectNotFoundException $e) {
                return [];
            }
        }


        public function getListByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getListByLogic($logic, $expires);
        }

        public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getListByQuery($query, $expires);
        }

        public function getPlainList($expires = Cache::DO_NOT_CACHE)
        {
            return parent::getPlainList($expires);
        }
        //@}

        /// custom list getters
        //@{
        public function getCustomList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE)
        {
            return parent::getCustomList($query, Cache::DO_NOT_CACHE);
        }

        public function getCustomRowList(SelectQuery $query)
        {
            return parent::getCustomRowList($query, Cache::DO_NOT_CACHE);
        }
        //@}

        /// query result getters
        //@{
        public function getQueryResult(SelectQuery $query)
        {
            return parent::getQueryResult($query, Cache::DO_NOT_CACHE);
        }
        //@}

        /// cachers
        //@{

        public function uncacheById($id)
        {
            return true;
        }

        /**
         * @return UncacherNullDaoWorker
         */
        public function getUncacherById($id)
        {
            return new UncacherNullDaoWorker();
        }
        //@}

        /// uncachers
        //@{

        public function uncacheByIds($ids)
        {
            return true;
        }

        public function uncacheByQuery(SelectQuery $query)
        {
            return true;
        }

        public function uncacheLists()
        {
            return true;
        }

        public function getCachedById($id)
        {
            return null;
        }

        protected function cacheById(
            Identifiable $object,
            $expires = Cache::DO_NOT_CACHE
        )
        {
            return $object;
        }
        //@}

        /// cache getters
        //@{

        protected function cacheByQuery(
            SelectQuery $query,
            /* Identifiable */
            $object,
            $expires = Cache::DO_NOT_CACHE
        )
        {
            return $object;
        }

        protected function getCachedByQuery(SelectQuery $query)
        {
            return null;
        }
        //@}
    }
}