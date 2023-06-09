<?php

use think\migration\Migrator;

class AuthGroup extends Migrator
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
        $table = $this->table('auth_group', array('comment' => '权限分组表'));
        $table->addColumn('title', 'string', array('limit' => 48, 'comment' => '分组名称'))
            ->addColumn('status', 'boolean', array('limit' => 1, 'default' => 1, 'comment' => '状态: 1正常 0禁用'))
            ->addColumn('rules', 'text', array('comment' => '用户组auth_rule表拥有的规则id,多个规则英文逗号隔开'))
            ->addColumn('son_rules', 'text', array('comment' => '用户组auth_rule表拥有的规则id,多个规则英文逗号隔开'))
            ->addColumn('describe', 'string', array('comment' => '描述'))
            ->addColumn('create_time', 'datetime', array('comment' => '创建时间'))
            ->addColumn('update_time', 'datetime', array('comment' => '更新时间'))
            ->create();
    }
}
