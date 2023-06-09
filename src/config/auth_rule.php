<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AuthRule extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('auth_rule', array('comment' => '权限规则表'));
        $table->addColumn('name', 'string', array('comment' => '规则唯一标识', 'limit' => 100))
            ->addColumn('title', 'string', array('comment' => '标题', 'limit' => 64))
            ->addColumn('status', 'boolean', array('limit' => 1, 'default' => 1, 'comment' => '状态: 1正常 0禁用'))
            ->addColumn('type', 'boolean', array('limit' => 1, 'default' => 1, 'comment' => ''))
            ->addColumn('condition', 'string', array('limit' => 100, 'default' => '', 'comment' => '规则表达式'))
            ->addColumn('pid', 'integer', array('comment' => '', 'default' => 0))
            ->addColumn('create_time', 'datetime', array('comment' => '创建时间'))
            ->addColumn('update_time', 'datetime', array('comment' => '更新时间'))
            ->addIndex(array('name'))
            ->create();
    }
}
