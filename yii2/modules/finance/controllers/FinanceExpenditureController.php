<?php

namespace app\modules\finance\controllers;

use Yii;
use kartik\mpdf\Pdf;
use yii\base\Model;
use app\modules\finance\Module;
use app\modules\finance\models\FinanceExpenditure;
use app\modules\finance\models\FinanceExpenditureSearch;
use app\modules\finance\models\FinanceKae;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\finance\models\FinanceFpa;
use app\modules\finance\components\Integrity;
use app\modules\finance\components\Money;
use yii\base\Exception;
use app\modules\finance\models\FinanceKaewithdrawal;
use app\modules\finance\models\FinanceKaecredit;
use app\modules\finance\models\FinanceExpendwithdrawal;
use app\modules\finance\models\FinanceExpenditurestate;
use app\modules\finance\models\FinanceSupplier;
use app\modules\finance\models\FinanceInvoice;
use app\modules\finance\models\FinanceDeduction;
use app\modules\finance\models\FinanceExpenddeduction;
use app\modules\finance\models\FinanceState;
use yii\web\User;

/**
 * FinanceExpenditureController implements the CRUD actions for FinanceExpenditure model.
 */
class FinanceExpenditureController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return  ['verbs' =>  [   'class' => VerbFilter::className(),
                                'actions' => ['delete' => ['POST']]],
                'access' => [   'class' => AccessControl::className(),
                                'rules' =>  [
                                            [   'actions' => ['create', 'update', 'delete', 'forwardstate', 'updatestate', 'backwardstate'],
                                                'allow' => false,
                                                'roles' => ['@'],
                                                'matchCallback' => function ($rule, $action) {
                                                    return Integrity::isLocked(Yii::$app->session["working_year"]);
                                                },
                                                'denyCallback' => function ($rule, $action) {
                                                    Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The action is not permitted! The year is locked."));
                                                    return $this->redirect(['index']);
                                                }
                                            ],
                                                ['actions' => ['index'], 'allow' => true, 'roles' => ['financial_viewer']],
                                                ['allow' => true, 'roles' => ['financial_editor']]
                                            ]]
                ];
    }

    /**
     * Lists all FinanceExpenditure models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FinanceExpenditureSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $kaesListModel = FinanceKae::find()->all();
        $expendwithdrawals = [];
        $prefix = Yii::$app->db->tablePrefix;
        $expwithdr = $prefix . 'finance_expendwithdrawal';
        $wthdr = $prefix . "finance_kaewithdrawal";

        $kaewithdrsbalance = FinanceKaewithdrawal::getAllWithdrawalsBalance($kaesListModel, Yii::$app->session["working_year"]);

        foreach ($dataProvider->models as $expend_model) {
            $withdrawal_model = (new \yii\db\Query())
            ->select($expwithdr . '.*,'. $wthdr . '.*')
            ->from([$expwithdr, $wthdr])
            ->where($expwithdr . '.kaewithdr_id=' . $wthdr . '.kaewithdr_id AND' . ' exp_id =' . $expend_model['exp_id'])
            ->all();

            $invoice = FinanceInvoice::find()->where(['exp_id' => $expend_model['exp_id']])->one()['inv_id'];

            $expendwithdrawals[$expend_model['exp_id']]['WITHDRAWAL'] = $withdrawal_model;

            $expendwithdrawals[$expend_model['exp_id']]['INVOICE'] = $invoice;

            for ($i = 0; $i < count($withdrawal_model); $i++) {
                $kaewithdrawal = FinanceExpendwithdrawal::find()
                ->where(['exp_id' => $expend_model['exp_id'], 'kaewithdr_id' => $withdrawal_model[$i]['kaewithdr_id']])
                ->one();

                $kaecredit_id = FinanceKaewithdrawal::find()
                ->where(['kaewithdr_id' => $kaewithdrawal['kaewithdr_id']])
                ->one()['kaecredit_id'];

                $expendwithdrawals[$expend_model['exp_id']]['EXPENDWITHDRAWAL'][$i] = $kaewithdrawal['expwithdr_amount'] +
                $kaewithdrawal['expwithdr_amount']*Money::toDecimalPercentage($expend_model['fpa_value']);
            }
        }

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'kaes' => $kaesListModel,
            'expendwithdrawals' => $expendwithdrawals,
            'balances' => $kaewithdrsbalance,
        ]);
    }

    /**
     * Creates an array of the sum expenditures carried out in the withdrawalas of an RCN
     * @return array
     */
    private function withdrawalsData($kaewithdrawals)
    {
        $withdrawals_expendituressum['DECISION'] = array();
        $withdrawals_expendituressum['INITIAL'] = array();
        $withdrawals_expendituressum['EXPENDED'] = array();
        $withdrawals_expendituressum['AVAILABLE'] = array();
        foreach ($kaewithdrawals as $key=>$kaewithdrawal){
            $expended = FinanceExpendwithdrawal::getExpendituresSum($kaewithdrawal->kaewithdr_id);
            $substr_decision = $kaewithdrawal->kaewithdr_decision;
            if (strlen($substr_decision) > 22){
                $substr_decision = substr($kaewithdrawal->kaewithdr_decision, 0, 22) . '...';
            }
            array_push($withdrawals_expendituressum['DECISION'], $substr_decision);
            array_push($withdrawals_expendituressum['INITIAL'], Money::toCurrency($kaewithdrawal->kaewithdr_amount));
            array_push($withdrawals_expendituressum['EXPENDED'], Money::toCurrency($expended));
            array_push($withdrawals_expendituressum['AVAILABLE'], Money::toCurrency($kaewithdrawal->kaewithdr_amount - $expended));
        }
        
        return $withdrawals_expendituressum;
    }
    

    /**
     * Creates a new FinanceExpenditure model for the RCN with number $id.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */
    public function actionCreate($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The requested expenditure could not be found."));
            return $this->redirect(['/finance/finance-kaewithdrawal/index']);
        }

        $suppliers = FinanceSupplier::find()->orderBy('suppl_name')->all();
        $kaecredit_id = FinanceKaecredit::find()->where(['kae_id' => $id, 'year' => Yii::$app->session["working_year"]])->one()->kaecredit_id;

        $kaewithdrawals = FinanceKaewithdrawal::find()->where(['kaecredit_id' => $kaecredit_id])->all();

        $withdrawals_expendituressum = $this->withdrawalsData($kaewithdrawals);
        
        $i = 0;
        $expendwithdrawals_models = [];
        foreach ($kaewithdrawals as $key=>$kaewithdrawal) {
            $kaewithdrawal->kaewithdr_amount = Money::toCurrency($kaewithdrawal->kaewithdr_amount, true);
            if (FinanceExpendwithdrawal::getWithdrawalBalance($kaewithdrawal->kaewithdr_id) > 0) {
                $expendwithdrawals_models[$i++] = new FinanceExpendwithdrawal();
            } else {
                unset($kaewithdrawals[$key]);
            }
        }
        
        if (count($expendwithdrawals_models) == 0) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "There is no withdrawal for this RCN to create expenditure."));
            return $this->redirect(['index']);
        }

        $deductions = FinanceDeduction::find()->where(['deduct_obsolete' => false])->all();
        $expenddeduction_models = [];

        $standard_deductions_count = count(FinanceDeduction::getStandardFinanceDeductionsAlias());
        for ($i = $standard_deductions_count; $i <= count($deductions); $i++) { //$standard_deductions_count for the number of standard deductions presented as radiolist
            $expenddeduction_models[$i-$standard_deductions_count] = new FinanceExpenddeduction();
        }
        
        $model = new FinanceExpenditure();
        $vat_levels = FinanceFpa::find()->all();

        foreach ($vat_levels as $vat_level) {
            $vat_level->fpa_value = Money::toPercentage($vat_level->fpa_value);
        }

        if ($model->load(Yii::$app->request->post())
            && Model::loadMultiple($expendwithdrawals_models, Yii::$app->request->post())
            && Model::loadMultiple($expenddeduction_models, Yii::$app->request->post())) {
            //$this->saveModels($model, $expendwithdrawals_models, $expenddeduction_models);
            if (!$this->saveModels($model, $expendwithdrawals_models, $expenddeduction_models)) {
                Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The expenditure was not saved. Please correct the assigned withdrawals (at least one and no duplicates)."));
                return $this->render('create', [
                    'model' => $model,
                    'expendwithdrawals_models' => $expendwithdrawals_models,
                    'vat_levels' => $vat_levels,
                    'kaewithdrawals' => $kaewithdrawals,
                    'suppliers' => $suppliers,
                    'expenddeduction_models' => $expenddeduction_models,
                    'deductions' => $deductions,
                    'withdrawals_expendituressum' => $withdrawals_expendituressum
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'expendwithdrawals_models' => $expendwithdrawals_models,
                'vat_levels' => $vat_levels,
                'kaewithdrawals' => $kaewithdrawals,
                'suppliers' => $suppliers,
                'expenddeduction_models' => $expenddeduction_models,
                'deductions' => $deductions,
                'withdrawals_expendituressum' => $withdrawals_expendituressum
            ]);
        }
    }

    /**
     * Updates an existing FinanceExpenditure model for the expenditure with id $id.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param integer $id
     * @return mixed
     */

    public function actionUpdate($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The requested expenditure could not be found."));
            return $this->redirect(['/finance/finance-kaewithdrawal/index']);
        }

        $statescount = FinanceExpenditurestate::find()->where(['exp_id' => $id])->count();
        if ($statescount > 1) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The expenditure could not be updated because is not in initial state."));
            return $this->redirect(['/finance/finance-expenditure/index']);
        }

        $model = $this->findModel($id);

        $suppliers = FinanceSupplier::find()->all();

        $kaewithdr_id = FinanceExpendwithdrawal::find()->where(['exp_id' => $id])->all()[0]->kaewithdr_id;
        $kaecredit_id = FinanceKaewithdrawal::find()->where(['kaewithdr_id' => $kaewithdr_id])->all()[0]->kaecredit_id;
        $kaewithdrawals = FinanceKaewithdrawal::find()->where(['kaecredit_id' => $kaecredit_id])->all();
        
        $withdrawals_expendituressum = $this->withdrawalsData($kaewithdrawals);
                
        $i = 0;
        $expendwithdrawals_models = FinanceExpendwithdrawal::find()->where(['exp_id' => $id])->orderBy('expwithdr_order')->all();
        $i = count($expendwithdrawals_models);
        foreach ($kaewithdrawals as $key=>$kaewithdrawal) {
            $kaewithdrawal->kaewithdr_amount = Money::toCurrency($kaewithdrawal->kaewithdr_amount, true);
            if (FinanceExpendwithdrawal::getWithdrawalBalance($kaewithdrawal->kaewithdr_id) > 0 ||
                !is_null(FinanceExpendwithdrawal::findOne(['exp_id' => $id, 'kaewithdr_id' => $kaewithdrawal->kaewithdr_id]))) {
                $exp_withdr_exists = FinanceExpendwithdrawal::find()->where(['exp_id' => $id])->andWhere(['kaewithdr_id' => $kaewithdrawal->kaewithdr_id])->one();

                if (is_null($exp_withdr_exists))
                    $expendwithdrawals_models[$i++] = new FinanceExpendwithdrawal();
                
            } else {
                unset($kaewithdrawals[$key]);
            }
        }

        if (count($expendwithdrawals_models) == 0) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "There is no withdrawal for this RCN to create expenditure."));
            return $this->redirect(['index']);
        }

        $deductions = FinanceDeduction::find()->where(['deduct_obsolete' => false])->all();
        $expenddeduction_models = [];
        $standard_deductions_ids = FinanceDeduction::getStandardFinanceDeductionsIds();        
        $exp_deduction = FinanceExpenddeduction::find()->where(['exp_id' => $id])->andWhere(['in', 'deduct_id', $standard_deductions_ids])->one();

        $index = 0;
        if (count($exp_deduction)) {
            $expenddeduction_models[$index++] = $exp_deduction;
        }
        
        foreach ($deductions as $deduction) {
            if(!in_array($deduction->deduct_id, $standard_deductions_ids)) {
                $exp_deductions_checkbox = FinanceExpenddeduction::find()->where(['exp_id' => $id, 'deduct_id'=> $deduction->deduct_id])->one();
                if (count($exp_deductions_checkbox)) {
                    $expenddeduction_models[$index++] = $exp_deductions_checkbox;
                } else {
                    $expenddeduction_models[$index] = new FinanceExpenddeduction();
                    $expenddeduction_models[$index++]->exp_id = $id;
                }
            }
        }

        $vat_levels = FinanceFpa::find()->all();

        foreach ($vat_levels as $vat_level) {
            $vat_level->fpa_value = Money::toPercentage($vat_level->fpa_value);
        }

        
        if ($model->load(Yii::$app->request->post())
            && Model::loadMultiple($expendwithdrawals_models, Yii::$app->request->post())
            && Model::loadMultiple($expenddeduction_models, Yii::$app->request->post())) {
            
            if (!$this->saveModels($model, $expendwithdrawals_models, $expenddeduction_models, false)) {
                Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The expenditure was not saved. Please correct the assigned withdrawals (at least one and no duplicates)."));
                //echo $model->exp_amount; die();
                return $this->render('update', [
                    'model' => $model,
                    'expendwithdrawals_models' => $expendwithdrawals_models,
                    'vat_levels' => $vat_levels,
                    'kaewithdrawals' => $kaewithdrawals,
                    'suppliers' => $suppliers,
                    'expenddeduction_models' => $expenddeduction_models,
                    'deductions' => $deductions,
                    'withdrawals_expendituressum' => $withdrawals_expendituressum
                ]);
            }
        } else {
            return $this->render('update', [
                    'model' => $model,
                    'expendwithdrawals_models' => $expendwithdrawals_models,
                    'vat_levels' => $vat_levels,
                    'kaewithdrawals' => $kaewithdrawals,
                    'suppliers' => $suppliers,
                    'expenddeduction_models' => $expenddeduction_models,
                    'deductions' => $deductions,
                    'withdrawals_expendituressum' => $withdrawals_expendituressum
            ]);
        }
    }


    /**
     * Updates an existing FinanceExpenditure model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    private function saveModels($model, $expendwithdrawals_models, $expenddeduction_models, $new_expenditure = true)
    {
        try {
            $model->exp_amount = Money::toCents($model->exp_amount);
            $model->fpa_value = Money::toDbPercentage($model->fpa_value);
            $model->exp_date = date("Y-m-d H:i:s");
            $model->exp_deleted = 0;
            $model->exp_lock = 0;
            
            $no_withdrawals_selected = true;
            for($i = 0; $i < count($expendwithdrawals_models); $i++){
                if($expendwithdrawals_models[$i]->kaewithdr_id == null)
                    continue;
                $no_withdrawals_selected = false;
                for($j = $i + 1; $j < count($expendwithdrawals_models); $j++){
                    if($expendwithdrawals_models[$i]->kaewithdr_id == $expendwithdrawals_models[$j]->kaewithdr_id)
                        return false;
                }
            }
            if($no_withdrawals_selected)
                return false;            
            
            $transaction = Yii::$app->db->beginTransaction();
            if (!$new_expenditure) {
                $old_expendwithdrawals = FinanceExpendwithdrawal::findAll(['exp_id' => $model->exp_id]);
                foreach ($old_expendwithdrawals as $old_expendwithdrawal) {
                    $old_expendwithdrawal->expwithdr_amount = 0;
                    unset($old_expendwithdrawal->expwithdr_order);
                    if (!$old_expendwithdrawal->save()) {
                        throw new Exception("Error in deleting old assignment of the expenditure withdrawals");
                    }
                }
            }                        

            if (!$model->save()) {
                throw new Exception("Error saving in the database.");
            }

            if (!$new_expenditure) {
                $standard_deductions_ids = FinanceDeduction::getStandardFinanceDeductionsIds();
                $old_expdeductions = FinanceExpenddeduction::find()->where(['exp_id' => $model->exp_id])
                                                                    ->andWhere(['NOT IN', 'deduct_id', $standard_deductions_ids])->all();
                
                foreach ($old_expdeductions as $old_expdeduction) {
                    $delete_it = false;
                    foreach ($expenddeduction_models as $expenddeduction_model) {
                        if ($expenddeduction_model->deduct_id == $old_expdeduction->deduct_id) {
                            $delete_it = true;
                        }
                    }

                    if (!$delete_it) {
                        if (!$old_expdeduction->delete()) {
                            throw new Exception("Error deleting previous deduction.");
                        }
                    }
                }
            } else {
                $expend_state_model = new FinanceExpenditurestate();
                $expend_state_model->exp_id = $model->exp_id;
                $expend_state_model->state_id = 1;
                $expend_state_model->expstate_date = date("Y-m-d H:i:s");
                if (!$expend_state_model->save()) {
                    throw new Exception("Error in setting the state of the expenditure.");
                }
            }

            $tmp_array = [];
            for ($i = 0; $i < count($expenddeduction_models); $i++) {
                $expenddeduction_models[$i]->exp_id = $model->exp_id;
                $tmp = $expenddeduction_models[$i]->deduct_id;

                if (isset($tmp) && $tmp != 0 && $tmp != null) {
                    if (!$expenddeduction_models[$i]->save()) {
                        throw new Exception("Error in assigning deductions to the expenditure.");
                    }
                }
            }
           
            $fpa = Money::toDecimalPercentage($model->fpa_value);
            $partial_amount = $model->exp_amount;

            foreach ($expendwithdrawals_models as $expendwithdrawals_model) {
                if($expendwithdrawals_model->kaewithdr_id == null)
                    continue;
                $withdrawal_balance = FinanceExpendwithdrawal::getWithdrawalBalance($expendwithdrawals_model->kaewithdr_id);     
                
                $expendwithdrawals_model->exp_id = $model->exp_id;
                if (($partial_amount + $partial_amount*$fpa) > $withdrawal_balance) {  
                    $expendwithdrawals_model->expwithdr_amount = floor($withdrawal_balance/(1+$fpa));
                    $partial_amount = $partial_amount - $expendwithdrawals_model->expwithdr_amount;
                    if(count($exist_model = FinanceExpendwithdrawal::findOne(['exp_id' => $expendwithdrawals_model['exp_id'], 'kaewithdr_id' => $expendwithdrawals_model['kaewithdr_id']])) != 0){
                        $exist_model->expwithdr_amount = $expendwithdrawals_model['expwithdr_amount'];
                        $exist_model->expwithdr_order = $expendwithdrawals_model->expwithdr_order;
                        if(!$exist_model->save()){
                            print_r($exist_model->errors); die();
                            throw new Exception("Error in assigning withdrawals to exceptions1.");
                        }
                    }
                    else if (!$expendwithdrawals_model->save()) {
                        throw new Exception("Error in assigning withdrawals to exceptions2.");
                    }
                } 
                else if(($partial_amount + $partial_amount*$fpa) <= $withdrawal_balance) {
                    $expendwithdrawals_model->expwithdr_amount = $partial_amount;              
                    if(count($exist_model = FinanceExpendwithdrawal::findOne(['exp_id' => $expendwithdrawals_model['exp_id'], 'kaewithdr_id' => $expendwithdrawals_model['kaewithdr_id']])) != 0){
                        $exist_model->expwithdr_amount = $partial_amount;
                        $exist_model->expwithdr_order = $expendwithdrawals_model->expwithdr_order;
                        
                        if(!$exist_model->save())
                            throw new Exception("Error in assigning withdrawals to exceptions3.");
                    }
                    else if (!$expendwithdrawals_model->save()) {
                        throw new Exception("Error in assigning withdrawals to exceptions4.");
                    }
                    $partial_amount = 0;
                    break;
                }
            }
            $zeroamount_expendwithdrawals = FinanceExpendwithdrawal::findAll(['exp_id' => $model->exp_id, 'expwithdr_amount' => 0]);
            foreach ($zeroamount_expendwithdrawals as $zeroamount_expendwithdrawal){
                if(!$zeroamount_expendwithdrawal->delete()){
                    throw new Exception("Error in deleting old assignment of the expenditure withdrawals");
                }
            }
            
            if ($partial_amount > 0) {
                throw new Exception("Amount of the expenditure is too high for the available withdrawals.");
            }
            
            $transaction->commit();

            $user = Yii::$app->user->identity->username;
            $year = Yii::$app->session["working_year"];
            $action = ($new_expenditure == true)? "created new expenditure." : "updated expenditure with id " . $model->exp_id;
            Yii::info('User ' . $user . ' working in year ' . $year . ' ' .  $action, 'financial');

            if ($new_expenditure) {
                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure was created successfully."));
            } else {
                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure was updated successfully."));
            }

            return $this->redirect(['index']);
        } catch (Exception $e) {
            $transaction->rollBack();  
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', $e->getMessage()));
            return $this->redirect(['index']);
        }
    }

    /**
     * Deletes an existing FinanceExpenditure model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The requested expenditure could not be found."));
            return $this->redirect(['/finance/finance-kaewithdrawal/index']);
        }

        $statescount = FinanceExpenditurestate::find()->where(['exp_id' => $id])->count();
        if ($statescount > 1) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The expenditure could not be deleted because is not in initial state."));
            return $this->redirect(['/finance/finance-expenditure/index']);
        }

        $expenditure = $this->findModel($id);
        try {
            $transaction = Yii::$app->db->beginTransaction();
            if (!FinanceExpenddeduction::deleteAll(['exp_id' => $expenditure->exp_id])) {
                throw new Exception();
            }
            if (!FinanceExpendwithdrawal::deleteAll(['exp_id' => $expenditure->exp_id])) {
                throw new Exception();
            }
            if (!FinanceExpenditurestate::deleteAll(['exp_id' => $expenditure->exp_id])) {
                throw new Exception();
            }
            if (FinanceInvoice::find(['exp_id' => $expenditure->exp_id])->where(['exp_id' => $expenditure->exp_id])->count() != 0) {
                if (!FinanceInvoice::deleteAll(['exp_id' => $expenditure->exp_id])) {
                    throw new Exception();
                }
            }
            if (!$expenditure->delete()) {
                throw new Exception();
            }
            $transaction->commit();

            $user = Yii::$app->user->identity->username;
            $year = Yii::$app->session["working_year"];
            Yii::info('User ' . $user . ' working in year ' . $year . ' deleted expenditure with id ' . $id, 'financial');

            Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure was deleted successfully."));
            return $this->redirect(['index']);
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failed to delete expenditure."));
            return $this->redirect(['index']);
        }
        return $this->redirect(['index']);
    }

    /**
     * Sets the expenditure state to the next state (e.g. if it is in the "Initial" state, then the
     * state is set to "Demanded")
     * If the action is successful, the next visual indicator will be shown.
     * @param integer $id
     * @return mixed
     */
    public function actionForwardstate($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The requested expenditure could not be found."));
            return $this->redirect(['/finance/finance-expenditure/index']);
        }

        $invoice = FinanceInvoice::findOne(['exp_id' => $id]);
        if (is_null($invoice)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The state of the expenditure cannot change. Please create voucher first."));
            return $this->redirect(['index']);
        }

        $exp_model = $this->findModel($id);
        $current_state = FinanceExpenditurestate::find()->where(['exp_id' => $exp_model->exp_id])->max('state_id');
        $current_state_name = FinanceState::findOne(['state_id' => $current_state+1])['state_name'];

        $state_model = new FinanceExpenditurestate();
        $state_model->exp_id = $exp_model->exp_id;

        if ($state_model->load(Yii::$app->request->post())) {
            try {
                $statescount = FinanceExpenditurestate::find()->where(['exp_id' => $state_model->exp_id])->count();
                if ($statescount < 0 || $statescount >= 4) {
                    throw new Exception();
                }
                $state_model->state_id = $statescount + 1;
                if (!$state_model->save()) {
                    throw new Exception();
                }

                $user = Yii::$app->user->identity->username;
                $year = Yii::$app->session["working_year"];
                Yii::info('User ' . $user . ' working in year ' . $year . ' forwarded state of expenditure with id ' . $id, 'financial');

                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure's state changed successfully."));
                return $this->redirect(['index']);
            } catch (Exception $e) {
                Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failed to change expenditure's state."));
                return $this->redirect(['index']);
            }
        } else {
            return $this->render('forwardstate', [
                'state_model' => $state_model,
                'current_state_name' => $current_state_name,
                'state_id' => $current_state
            ]);
        }
    }


    /**
     * Sets the expenditure state to the next state (e.g. if it is in the "Initial" state, then the
     * state is set to "Demanded")
     * If the action is successful, the next visual indicator will be shown.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdatestate($state_id, $exp_id)
    {
        try {
            $state_model = FinanceExpenditurestate::findOne(['state_id' => $state_id, 'exp_id' => $exp_id]);

            if (is_null($state_model)) {
                throw new Exception();
            }

            $current_state_name = FinanceState::findOne(['state_id' => $state_id])['state_name'];

            if ($state_model->load(Yii::$app->request->post())) {
                if (!$state_model->save()) {
                    throw new Exception();
                }

                $user = Yii::$app->user->identity->username;
                $year = Yii::$app->session["working_year"];
                Yii::info('User ' . $user . ' working in year ' . $year . ' updated the details of state (state_id=' . $state_id . ') for the expenditure with id ' . $exp_id, 'financial');

                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure's state details were updated successfully."));
                return $this->redirect(['index']);
            } else {
                return $this->render('updatestate', [
                    'state_model' => $state_model,
                    'current_state_name' => $current_state_name,
                    'state_id' => $state_id
                ]);
            }
        } catch (Exception $e) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failed to update expenditure's state details."));
            return $this->redirect(['index']);
        }
    }


    /**
     * Sets the expenditure state to the next state (e.g. if it is in the "Demanded" state, then the
     * state is set to "Initial")
     * If the action is successful, the visual indicators will be shown appropriately.
     * @param integer $id
     * @return mixed
     */
    public function actionBackwardstate($id)
    {
        if (!isset($id) || !is_numeric($id)) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The requested expenditure could not be found."));
            return $this->redirect(['/finance/finance-expenditure/index']);
        }

        try {
            $statescount = FinanceExpenditurestate::find()->where(['exp_id' => $id])->count();
            if ($statescount <= 1 || $statescount > 4) {
                throw new Exception();
            }
            if (!FinanceExpenditureState::find()->
                where(['exp_id' => $id, 'state_id' => $statescount])->one()->delete()) {
                throw new Exception();
            }

            $user = Yii::$app->user->identity->username;
            $year = Yii::$app->session["working_year"];
            Yii::info('User ' . $user . ' working in year ' . $year . ' backwarded state of expenditure with id ' . $id, 'financial');

            Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The expenditure's state changed successfully."));
            return $this->redirect(['index']);
        } catch (Exception $e) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failed to change expenditure's state."));
            return $this->redirect(['index']);
        }
    }


    /**
     * Creates the Expedinture Payment Report (pdf file) for the expenditure with $id
     * @param integer $id
     * @return mixed
     */
    public function actionPaymentreport()
    {
        $models = [];
        $kae = "";
        $exp_ids = Yii::$app->request->post('selection');
        $first_expenditure = true;
        $maxdate = null;
        try {
            if (is_null($exp_ids)) {
                throw new Exception();
            }
            foreach ($exp_ids as $index=>$id) {
                $expenditure_model = FinanceExpenditure::findOne(['exp_id' => $id]);
                $supplier_model = FinanceSupplier::findOne(['suppl_id' => $expenditure_model['suppl_id']]);
                $invoice_model = FinanceInvoice::findOne(['exp_id' => $expenditure_model['exp_id']]);

                $expstate2 = FinanceExpenditurestate::findOne(['exp_id' => $id, 'state_id' => 2]);
                if(is_null($expstate2))
                    throw new Exception();                                  
                $expstate2_date = $expstate2->expstate_date;
                
                if ($first_expenditure) {
                    $maxdate = $expstate2_date;
                    $first_expenditure = false;
                }
                if ($expstate2_date > $maxdate) {
                    $maxdate = $expstate2_date;
                }

                //echo "<pre>"; print_r($maxdate); echo "</pre>"; die();

                $deductions = FinanceExpenddeduction::find()->where(['exp_id' => $id])->all();
                $deductions_models = [];
                foreach ($deductions as $deduct_index=>$deduction) {
                    $deductions_models[$deduct_index] = FinanceDeduction::findOne(['deduct_id' => $deduction->deduct_id]);
                }

                if (is_null($invoice_model)) {
                    throw new Exception();
                }

                $models[$index]['EXPENDITURE'] = $expenditure_model;
                $models[$index]['SUPPLIER'] = $supplier_model;
                $models[$index]['INVOICE'] = $invoice_model;
                $models[$index]['DEDUCTIONS'] = $deductions_models;

                $kaewithdr_id = FinanceExpendwithdrawal::find()->
                                    where(['exp_id' => $expenditure_model['exp_id']])->all()[0]['kaewithdr_id'];
                $kaecredit_id = FinanceKaewithdrawal::findOne(['kaewithdr_id' => $kaewithdr_id])['kaecredit_id'];
                $exp_kae = FinanceKaecredit::findOne(['kaecredit_id' => $kaecredit_id])['kae_id'];
                if ($kae == "") {
                    $kae = $exp_kae;
                } elseif ($exp_kae != $kae) {
                    throw new Exception();
                }
            }
            $year = Yii::$app->session["working_year"];
        } catch (Exception $e) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failed to create Payments Report. Please check the selected expenditures (should be of the same RCN, to have an assigned voucher and not be in initial state)."));
            return $this->redirect(['index']);
        }

        $content = $this->renderPartial('paymentreport', [
            'models' => $models,
            'year' => $year,
            'kae' => $kae,
            'maxdate' => $maxdate
        ]);

        $user = Yii::$app->user->identity->username;
        $year = Yii::$app->session["working_year"];
        Yii::info('User ' . $user . ' working in year ' . $year . ' created payment report.', 'financial');

        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8,
            'format' => Pdf::FORMAT_A4,
            'orientation' => Pdf::ORIENT_LANDSCAPE,
            'filename' => 'aitisi.pdf',
            'destination' => Pdf::DEST_DOWNLOAD,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'options' => ['title' => 'Περιφερειακή Διεύθυνση Πρωτοβάθμιας και Δευτεροβάθμιας Εκπαίδευσης Κρήτης'],
        ]);
        return $pdf->render();
    }

    public function actionCoversheet()
    {
        try {
            $exp_ids = Yii::$app->request->post('selection');
            //echo "<pre>"; print_r($exp_ids); echo "</pre>"; die();
            $supplier = null;
            $expenditures_model = array();
            $kae = null;
            $exp_state = null;
            
            foreach ($exp_ids as $key=>$exp_id)
            {
                $expenditure = FinanceExpenditure::find()->where(['exp_id' => $exp_id])->one();
                $expenditures_model[$key]['EXPENDITURE'] = $expenditure;
                $expenditures_model[$key]['DEDUCTIONS'] = $expenditure->getDeductsSumAmount();                   
                
                $curr_expstate = FinanceExpenditurestate::find()->where(['exp_id' => $exp_id]);

                $demanded_state = 2;
                if($curr_expstate->max('state_id') != $demanded_state)
                    throw new Exception(Module::t('modules/finance/app', "Failed to create cover sheet. At least one expenditure is not in the proper state."));
                else {
                    $curr_expstate = $curr_expstate->andWhere(['state_id' => $demanded_state])->one();
                    if($exp_state == null)
                        $exp_state = $curr_expstate;
                    else if(($exp_state['expstate_date'] != $curr_expstate['expstate_date']) ||
                            ($exp_state['expstate_protocol'] != $curr_expstate['expstate_protocol']))
                            throw new Exception(Module::t('modules/finance/app', "Failed to create cover sheet. The demand dates of expenditures do not have the same date or protocol."));
                }
                                
                $curr_supplier = $expenditure['suppl_id'];
                if($supplier == null){
                    $supplier = $curr_supplier;
                }
                else if($curr_supplier != $supplier)
                    throw new Exception(Module::t('modules/finance/app', "Failed to create cover sheet. Please select expenditures of the same supplier."));                               
            
                $curr_kae = $expenditure->getKae();                
                if($kae == null){                    
                    $kae = $curr_kae;
                }
                else if($curr_kae['kae_id'] != $kae['kae_id']){                     
                    throw new Exception(Module::t('modules/finance/app', "Failed to create cover sheet. Please select expenditures of the same RCN."));
                }
            }

            $expstate_model = $curr_expstate;            
            $supplier_model = FinanceSupplier::findOne(['suppl_id' => $supplier]);

            $content = $this->renderPartial('coversheet',
                                            ['expenditures_model' => $expenditures_model,
                                             'expstate_model' => $expstate_model,
                                             'supplier_model' => $supplier_model,
                                             'kae' => $curr_kae['kae_id']
                                            ]);

            $user = Yii::$app->user->identity->username;
            $year = Yii::$app->session["working_year"];
            Yii::info('User ' . $user . ' working in year ' . $year . ' created cover sheet for expenditure with id ' . $exp_ids[0], 'financial');

            $pdf = new Pdf([
                'mode' => Pdf::MODE_UTF8,
                'format' => Pdf::FORMAT_A4,
                'orientation' => Pdf::ORIENT_PORTRAIT,
                'filename' => 'aitisi.pdf',
                'destination' => Pdf::DEST_DOWNLOAD,
                'content' => $content,
                'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
                'cssInline' => '.kv-heading-1{font-size:18px}',
                'options' => ['title' => 'Περιφερειακή Διεύθυνση Πρωτοβάθμιας και Δευτεροβάθμιας Εκπαίδευσης Κρήτης'],
            ]);
            return $pdf->render();
        } catch (Exception $e) {
            Yii::$app->session->addFlash('danger', $e->getMessage());
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the FinanceExpenditure model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FinanceExpenditure the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FinanceExpenditure::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}