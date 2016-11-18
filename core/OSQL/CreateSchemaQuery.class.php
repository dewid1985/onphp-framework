<?php

namespace OnPhp {
    /**
     * Created by PhpStorm.
     * User: root
     * Date: 17.11.16
     * Time: 16:10
     */
    class CreateSchemaQuery extends QueryIdentification
    {
        private $schema;

        function __construct($schema)
        {
            $this->schema = $schema;
        }

        public function toDialectString(Dialect $dialect)
        {
            $out = 'CREATE SCHEMA IF NOT EXISTS ' . $dialect->quoteSchema($this->schema) . ';';

            return $out;
        }
    }
}