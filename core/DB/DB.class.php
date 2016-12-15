<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
namespace OnPhp {
    /**
     * DB-connector's implementation basis.
     *
     * @ingroup DB
     **/
    abstract class DB
    {
        const FULL_TEXT_AND = 1;
        const FULL_TEXT_OR = 2;

        protected $link = null;
        protected $dialect = null;

        protected $persistent = false;

        // credentials
        protected $username = null;
        protected $password = null;
        protected $hostname = null;
        protected $port = null;
        protected $basename = null;
        protected $encoding = null;

        /**
         * flag to indicate whether we're in transaction
         **/
        private $transaction = false;
        /**
         * @var array list of all started savepoints
         */
        private $savepointList = array();

        private $queue = array();
        private $toQueue = false;
        /**
         * @var UncachersPool
         */
        private $uncacher = null;
        private $outOfTransactionCachePeer = null;

        abstract public function connect();

        abstract public function disconnect();

        abstract public function getTableInfo($table);

        abstract public function queryRaw($queryString);

        abstract public function queryRow(Query $query);

        abstract public function querySet(Query $query);

        abstract public function queryColumn(Query $query);

        abstract public function queryCount(Query $query);

        // actually set's encoding
        abstract public function setDbEncoding();

        /**
         * @return Dialect
         */
        abstract protected function spawnDialect();

        public function __destruct()
        {
            if ($this->isConnected()) {
                if ($this->transaction)
                    $this->rollback();

                if (!$this->persistent)
                    $this->disconnect();
            }
        }

        public function getDialect()
        {
            return $this->dialect = $this->dialect
                ?: ($this->spawnDialect()->setDB($this));
        }

        /**
         * Shortcut
         *
         * @param $connector
         * @param $user
         * @param $pass
         * @param $host
         * @param null $base
         * @param bool $persistent
         * @param null $encoding
         * @return DB
         */
        public static function spawn(
            $connector, $user, $pass, $host,
            $base = null, $persistent = false, $encoding = null
        )
        {
            /** @var DB $db */
            $db = new $connector;

            $db
                ->setUsername($user)
                ->setPassword($pass)
                ->setHostname($host)
                ->setBasename($base)
                ->setPersistent($persistent)
                ->setEncoding($encoding);

            return $db;
        }

        public function getLink()
        {
            return $this->link;
        }

        /**
         * transaction handling
         * @deprecated by Transaction class
         **/
        //@{
        /**
         * @return DB
         **/
        public function begin(
            /* IsolationLevel */
            $level = null,
            /* AccessMode */
            $mode = null
        )
        {
            $begin = 'begin';

            if ($level && $level instanceof IsolationLevel)
                $begin .= ' ' . $level->toString();

            if ($mode && $mode instanceof AccessMode)
                $begin .= ' ' . $mode->toString();

            if ($this->toQueue)
                $this->queue[] = $begin;
            else
                $this->queryRaw("{$begin};\n");

            $this->transaction = true;

            $this->outOfTransactionCachePeer = Cache::me();
            Cache::setPeer(Cache::me()->getRuntimeCopy());

            return $this;
        }

        /**
         * @return DB
         **/
        public function commit() : DB
        {
            if ($this->toQueue)
                $this->queue[] = 'commit;';
            else
                $this->queryRaw("commit;\n");

            $this->transaction = false;
            $this->savepointList = array();

            Cache::setPeer(Cache::me()->getRuntimeCopy());
            $this->triggerUncacher();

            return $this;
        }

        /**
         * @return DB
         **/
        public function rollback() : DB
        {
            if ($this->toQueue)
                $this->queue[] = 'rollback;';
            else
                $this->queryRaw("rollback;\n");

            $this->transaction = false;
            $this->savepointList = array();

            Cache::setPeer(Cache::me()->getRuntimeCopy());
            $this->triggerUncacher();

            return $this;
        }

        public function inTransaction()
        {
            return $this->transaction;
        }
        //@}

        /**
         * queue handling
         * @deprecated by Queue class
         **/
        //@{
        /**
         * @return DB
         **/
        public function queueStart() : DB
        {
            if ($this->hasQueue())
                $this->toQueue = true;

            return $this;
        }

        /**
         * @return DB
         **/
        public function queueStop() : DB
        {
            $this->toQueue = false;

            return $this;
        }

        /**
         * @return DB
         **/
        public function queueDrop() : DB
        {
            $this->queue = array();

            return $this;
        }

        /**
         * @return DB
         **/
        public function queueFlush() : DB
        {
            if ($this->queue)
                $this->queryRaw(
                    implode(";\n", $this->queue)
                );

            $this->toQueue = false;

            return $this->queueDrop();
        }

        public function isQueueActive()
        {
            return $this->toQueue;
        }
        //@}

        /**
         * @param string $savepointName
         * @return DB
         * @throws DatabaseException
         */
        public function savepointBegin($savepointName) : DB
        {
            $this->assertSavePointName($savepointName);
            if (!$this->inTransaction())
                throw new DatabaseException('To use savepoint begin transaction first');

            $query = 'savepoint ' . $savepointName;
            if ($this->toQueue)
                $this->queue[] = $query;
            else
                $this->queryRaw("{$query};\n");

            return $this->addSavepoint($savepointName);
        }

        /**
         * @param string $savepointName
         * @return DB
         * @throws DatabaseException
         */
        public function savepointRelease($savepointName)
        {
            $this->assertSavePointName($savepointName);
            if (!$this->inTransaction())
                throw new DatabaseException('To release savepoint need first begin transaction');

            if (!$this->checkSavepointExist($savepointName))
                throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");

            $query = 'release savepoint ' . $savepointName;
            if ($this->toQueue)
                $this->queue[] = $query;
            else
                $this->queryRaw("{$query};\n");

            return $this->dropSavepoint($savepointName);
        }

        /**
         * @param string $savepointName
         * @return DB
         * @throws DatabaseException
         */
        public function savepointRollback($savepointName)
        {
            $this->assertSavePointName($savepointName);
            if (!$this->inTransaction())
                throw new DatabaseException('To rollback savepoint need first begin transaction');

            if (!$this->checkSavepointExist($savepointName))
                throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");

            $query = 'rollback to savepoint ' . $savepointName;
            if ($this->toQueue)
                $this->queue[] = $query;
            else
                $this->queryRaw("{$query};\n");

            return $this->dropSavepoint($savepointName);
        }

        /**
         * base queries
         **/
        //@{
        public function query(Query $query)
        {
            return
                $this
                    ->queryRaw(
                        $query
                            ->toDialectString(
                                $this->getDialect()
                            )
                    );
        }

        public function queryNull(Query $query)
        {
            if ($query instanceof SelectQuery)
                throw new WrongArgumentException(
                    'only non-select queries supported'
                );

            if ($this->toQueue) {
                $this->queue[] = $query->toDialectString($this->getDialect());
                return true;
            } else
                return $this->query($query);
        }

        //@}

        /**
         * @return bool
         */
        public function isConnected()  : bool
        {
            return is_resource($this->link);
        }

        /**
         * @return bool
         */
        public function hasSequences() : bool
        {
            return false;
        }

        /**
         * @return bool
         */
        public function hasQueue() :  bool
        {
            return true;
        }

        /**
         * @return bool
         */
        public function isPersistent() : bool
        {
            return $this->persistent;
        }

        /**
         * @return DB
         **/
        public function setPersistent($really = false)
        {
            $this->persistent = ($really === true);

            return $this;
        }

        /**
         * @return DB
         **/
        public function setUsername($name)
        {
            $this->username = $name;

            return $this;
        }

        /**
         * @return DB
         **/
        public function setPassword($password)
        {
            $this->password = $password;

            return $this;
        }

        /**
         * @return DB
         **/
        public function setHostname($host)
        {
            $port = null;

            if (strpos($host, ':') !== false)
                list($host, $port) = explode(':', $host, 2);

            $this->hostname = $host;
            $this->port = $port;

            return $this;
        }

        /**
         * @return DB
         **/
        public function setBasename($base)
        {
            $this->basename = $base;

            return $this;
        }

        /**
         * @return DB
         **/
        public function setEncoding($encoding)
        {
            $this->encoding = $encoding;

            return $this;
        }

        public function registerUncacher(UncacherBase $uncacher)
        {
            $uncacher->uncache();
            if ($this->inTransaction()) {
                $this->getUncacher()->merge($uncacher);
            }
        }

        /**
         * @param string $savepointName
         * @return $this
         * @throws DatabaseException
         */
        private function addSavepoint($savepointName)
        {
            if ($this->checkSavepointExist($savepointName))
                throw new DatabaseException("savepoint with name '{$savepointName}' already marked");

            $this->savepointList[$savepointName] = true;
            return $this;
        }

        /**
         * @param string $savepointName
         * @return $this
         * @throws DatabaseException
         */
        private function dropSavepoint($savepointName)
        {
            if (!$this->checkSavepointExist($savepointName))
                throw new DatabaseException("savepoint with name '{$savepointName}' nor registered");

            unset($this->savepointList[$savepointName]);
            return $this;
        }

        private function checkSavepointExist($savepointName)
        {
            return isset($this->savepointList[$savepointName]);
        }

        private function assertSavePointName($savepointName)
        {
            Assert::isEqual(1, preg_match('~^[A-Za-z][A-Za-z0-9]*$~iu', $savepointName));
        }

        /**
         * @return UncachersPool
         */
        private function getUncacher()
        {
            return $this->uncacher = $this->uncacher ?: new UncachersPool();
        }

        private function triggerUncacher()
        {
            if ($this->uncacher) {
                $this->uncacher->uncache();
                $this->uncacher = null;
            }
        }
    }
}