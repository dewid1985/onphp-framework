<?php

/**
 * Created by PhpStorm.
 * User: root
 * Date: 17.11.16
 * Time: 16:20
 */
namespace OnPhp {
    /**
     * Class MetaClassSchema
     * @package OnPhp
     */
    class MetaClassSchema
    {

        private $schema;

        function __construct($schema)
        {
            $this->schema = $schema;
        }

        public function buildSchema()
        {
            $out = <<<EOT
setSchema('$this->schema')
EOT;
            return $out;
        }
    }
}