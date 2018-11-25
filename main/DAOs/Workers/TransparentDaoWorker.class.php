<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * Basis for transparent DAO workers.
     *
     * @see VoodooDaoWorker for obscure and greedy worker.
     * @see SmartDaoWorker for less obscure locking-based worker.
     *
     * @ingroup DAOs
     **/
    abstract class TransparentDaoWorker extends CommonDaoWorker
    {
        public function getByLogic(LogicalObject $logic,  $expires = Cache::EXPIRES_FOREVER)
        {
            return parent::getByLogic($logic, $expires);
        }

        /// single object getters
        //@{

        public function getByQuery(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER )
        {
            try {
                return parent::getByQuery($query, $expires);
            } catch (CachedObjectNotFoundException $e) {
                throw $e;
            } catch (ObjectNotFoundException $e) {
                $this->cacheByQuery($query, Cache::NOT_FOUND);
                throw $e;
            }
        }

        public function getCustom(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER )
        {
            try {
                return parent::getCustom($query, Cache::EXPIRES_FOREVER);
            } catch (CachedObjectNotFoundException $e) {
                throw $e;
            } catch (ObjectNotFoundException $e) {
                $this->cacheByQuery($query, Cache::NOT_FOUND);
                throw $e;
            }
        }

        public function getListByIds(array $ids, $expires = Cache::NOT_FOUND)
        {
            $list = [];
            $toFetch = [];
            $prefixed = [];

            $proto = $this->dao->getProtoClass();

            $proto->beginPrefetch();

            // dupes, if any, will be resolved later @ ArrayUtils::regularizeList
            $ids = array_unique($ids);

            foreach ($ids as $id) {
                $prefixed[$id] = $this->makeIdKey($id);
            }

            if (
            $cachedList
                = Cache::me()->mark($this->className)->getList($prefixed)
            ) {
                foreach ($cachedList as $cached) {
                    if ($cached && ($cached !== Cache::NOT_FOUND)) {
                        $list[] = $this->dao->completeObject($cached);

                        unset($prefixed[$cached->getId()]);
                    }
                }
            }

            $toFetch += array_keys($prefixed);

            if ($toFetch) {
                $remainList = [];

                foreach ($toFetch as $id) {
                    try {
                        $remainList[] = $this->getById($id);
                    } catch (ObjectNotFoundException $e) {/*_*/
                    }
                }

                $list = array_merge($list, $remainList);
            }

            $proto->endPrefetch($list);

            return $list;
        }

        public function getById($id, $expires = Cache::EXPIRES_FOREVER)
        {
            try {
                return parent::getById($id, $expires);
            } catch (CachedObjectNotFoundException $e) {
                throw $e;
            } catch (ObjectNotFoundException $e) {
                $this->cacheNullById($id);
                throw $e;
            }
        }
        //@}

        /// object's list getters
        //@{

        protected function cacheNullById($id)
        {
            return
                Cache::me()
                    ->mark($this->className)
                    ->add(
                        $this->makeIdKey($id),
                        Cache::NOT_FOUND,
                        Cache::EXPIRES_FOREVER
                    );
        }

        public function getListByQuery(SelectQuery $query, $expires = Cache::NOT_FOUND)
        {
            $list = $this->getCachedList($query);

            if ($list) {
                if ($list === Cache::NOT_FOUND) {
                    throw new ObjectNotFoundException();
                } else {
                    return $list;
                }
            } else {
                if ($list = $this->fetchList($query)) {
                    return $this->cacheListByQuery($query, $list);
                } else {
                    $this->cacheListByQuery($query, Cache::NOT_FOUND);
                    throw new ObjectNotFoundException();
                }
            }

            Assert::isUnreachable();
        }

        protected function getCachedList(SelectQuery $query)
        {
            return
                $this->gentlyGetByKey(
                    $this->makeQueryKey($query, self::SUFFIX_LIST)
                );
        }

        abstract protected function gentlyGetByKey($key);
        //@}

        /// custom list getters
        //@{

        public function getListByLogic(LogicalObject $logic, $expires = Cache::EXPIRES_FOREVER)
        {
            return parent::getListByLogic($logic, $expires);
        }

        public function getPlainList($expires = Cache::EXPIRES_FOREVER )
        {
            return parent::getPlainList($expires);
        }
        //@}

        /// query result getters
        //@{

        public function getCustomList(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER)
        {
            try {
                return parent::getCustomList($query, Cache::EXPIRES_FOREVER);
            } catch (CachedObjectNotFoundException $e) {
                throw $e;
            } catch (ObjectNotFoundException $e) {
                $this->cacheByQuery($query, Cache::NOT_FOUND);
                throw $e;
            }
        }
        //@}

        /// cachers
        //@{

        public function getCustomRowList(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER)
        {
            try {
                return parent::getCustomRowList($query, $expires);
            } catch (CachedObjectNotFoundException $e) {
                throw $e;
            } catch (ObjectNotFoundException $e) {
                $this->cacheByQuery($query, Cache::NOT_FOUND);
                throw $e;
            }
        }
        //@}

        /// internal helpers
        //@{

        public function getQueryResult(SelectQuery $query, $expires = Cache::EXPIRES_FOREVER)
        {
            return parent::getQueryResult($query, Cache::EXPIRES_FOREVER);
        }

        protected function cacheById(
            Identifiable $object,
            $expires = Cache::EXPIRES_FOREVER
        )
        {
            Cache::me()
                ->mark($this->className)
                ->add(
                    $this->makeIdKey($object->getId()),
                    $object,
                    $expires
                );

            return $object;
        }

        protected function getCachedByQuery(SelectQuery $query)
        {
            return
                $this->gentlyGetByKey(
                    $this->makeQueryKey($query, self::SUFFIX_QUERY)
                );
        }

        protected function keyToInt($key)
        {
            // 7 == strlen(dechex(x86 PHP_INT_MAX)) - 1
            return hexdec(substr(md5($key), 0, 7)) + strlen($key);
        }
        //@}
    }
}