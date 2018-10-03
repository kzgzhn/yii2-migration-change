<?php

use kzgzhn\migrationchange\MigrationChangeBehavior;
use yii\db\Migration;

/**
 * Class m181003_125953_example
 *
 * Пример
 *
 * @mixin MigrationChangeBehavior
 */
class m181003_125953_example extends Migration
{
    public function change()
    {
        $this->change->createTable('user', [
            'id' => $this->primaryKey(),
            'login' => $this->string(),
        ]);

        $this->change->createTable('tmp-post', [
            'id' => $this->integer(),
            'author_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->defaultValue(1),
            'title' => $this->string(),
        ]);

        $this->change->renameTable('tmp-post', 'post');

        $this->change->addColumn('post', 'body-tmp', $this->text());

        $this->change->renameColumn('post', 'body-tmp', 'body');

        $this->change->addPrimaryKey('id', 'post', 'id');

        $this->change->addForeignKey(
            'fk-post-author_id',
            'post',
            'author_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->change->createIndex(
            'idx-post-category_id',
            'post',
            'category_id'
        );
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeUp()
    {
        $this->change->up();
    }

    /**
     * {@inheritdoc}
     * @throws \yii\base\Exception
     */
    public function safeDown()
    {
        $this->change->down();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            MigrationChangeBehavior::class,
        ];
    }
}
