<?php

use think\migration\Migrator;
use think\migration\db\Column;

class AuthGroupAccess extends Migrator
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
        $table = $this->table('auth_group_access', array('comment' => '用户分组表'));
        $table->addColumn('uid', 'integer', array('comment' => '用户ID'))
            ->addColumn('group_id', 'integer', array('comment' => '分组ID'))
            ->addColumn('create_time', 'datetime', array('comment' => '创建时间'))
            ->addColumn('update_time', 'datetime', array('comment' => '更新时间'))
            ->addIndex('uid')
            ->addIndex('group_id')
            ->addIndex(array('uid', 'group_id'), array('unique' => true))
            ->create();
    }
}
