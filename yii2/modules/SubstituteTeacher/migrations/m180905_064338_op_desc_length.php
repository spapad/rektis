<?php

use yii\db\Migration;
use app\traits\DbMigrates;

class m180905_064338_op_desc_length extends Migration
{
    use DbMigrates;

    public function safeUp()
    {
        $this->allowInvalidDates();

        $this->alterColumn('{{%stoperation}}', 'description', $this->string(500)->notNull()->defaultValue(''));
    }

    public function safeDown()
    {
        $this->allowInvalidDates();

        $this->alterColumn('{{%stoperation}}', 'description', $this->string(90)->notNull()->defaultValue(''));
    }
}
