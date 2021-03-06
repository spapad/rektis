<?php

namespace app\modules\SubstituteTeacher\models;

/**
 * This is the ActiveQuery class for [[Call]].
 *
 * @see Call
 */
class CallQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return Call[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Call|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
