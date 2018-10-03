<?php

namespace kzgzhn\migrationchange;

use yii\base\Behavior;
use yii\base\Exception;
use yii\db\Migration;

/**
 * Class MigrationChangeBehavior
 *
 * @property Migration $owner
 * @property MigrationChangeBehavior $change
 *
 * @mixin Migration
 *
 * @package kzgzhn\migrationchange
 * @author Arman Kazgozhin <arman.kazgozhin@gmail.com>
 */
class MigrationChangeBehavior extends Behavior
{
    /**
     * @var array
     */
    protected $buffer = [];

    /**
     * @var array
     */
    public $methods = [
        'createTable',
        'renameTable',
        'addColumn',
        'renameColumn',
        'addPrimaryKey',
        'addForeignKey',
        'createIndex',
    ];

    /**
     * @throws Exception
     */
    public function up()
    {
        $this->runChangeAction();

        array_map([$this, '_up'], $this->buffer);
    }

    /**
     * @throws Exception
     */
    public function down()
    {
        $this->runChangeAction();

        array_map([$this, '_down'], array_reverse($this->buffer));
    }

    /**
     * @param array $data
     */
    protected function _up($data)
    {
        call_user_func_array([$this->owner, $data[0]], $data[1]);
    }

    /**
     * @param array $data
     */
    protected function _down($data)
    {
        $method = $data[0];
        $args = $data[1];

        if (in_array($method, ['addColumn', 'addPrimaryKey', 'addForeignKey', 'createIndex'])) {
            $method = str_replace(['add', 'create'], 'drop', $method);
            $args = array_slice($args, 0, 2);
        } else if (in_array($method, ['renameTable', 'renameColumn'])) {
            $n = 2;
            $args = array_merge(array_slice($args, 0, count($args) - $n), array_reverse(array_slice($args, -$n)));
        } else if ($method == 'createTable') {
            $method = 'dropTable';
            $args = array_slice($args, 0, 1);
        }

        call_user_func_array([$this->owner, $method], $args);
    }

    /**
     * @return $this
     */
    public function getChange()
    {
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    protected function runChangeAction()
    {
        if (!method_exists($this->owner, 'change')) {
            throw new Exception(sprintf('Method `%s` not found', 'change'));
        }

        return $this->owner->change($this);
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function __call($name, $params)
    {
        if (!method_exists($this->owner, $name)) {
            return parent::__call($name, $params);
        }

        if (array_search($name, $this->methods) === false) {
            throw new Exception(sprintf('Deny method `%s`', $name));
        }

        $this->buffer[] = func_get_args();

        return $this;
    }
}
