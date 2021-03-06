<?php

namespace app\modules\SubstituteTeacher\models;

/**
 * This is the ActiveQuery class for [[OperationSpecialisation]].
 *
 * @see OperationSpecialisation
 */
class OperationSpecialisationQuery extends \yii\db\ActiveQuery
{

    /**
     * @inheritdoc
     * @return OperationSpecialisation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return OperationSpecialisation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
