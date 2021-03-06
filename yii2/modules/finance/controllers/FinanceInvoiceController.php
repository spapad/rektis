<?php

namespace app\modules\finance\controllers;

use Yii;
use app\modules\finance\Module;
use app\modules\finance\components\Integrity;
use app\modules\finance\models\FinanceInvoice;
use app\modules\finance\models\FinanceInvoiceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Exception;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\finance\models\FinanceExpenditurestate;
use app\modules\finance\models\FinanceSupplier;
use app\modules\finance\models\FinanceExpenditure;
use app\modules\finance\models\FinanceInvoicetype;

/**
 * FinanceInvoiceController implements the CRUD actions for FinanceInvoice model.
 */
class FinanceInvoiceController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [   'class' => AccessControl::className(),
                'rules' =>  [
                    [   'actions' => ['create', 'update', 'delete'],
                        'allow' => false,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return Integrity::isLocked(Yii::$app->session["working_year"]);
                        },
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "The action is not permitted! The year is locked."));
                            if (Yii::$app->request->get('expenditures_return') == 1) {
                                return $this->redirect(['/finance/finance-expenditure']);
                            }
                            return $this->redirect(['/finance/finance-invoice']);
                        }
                        ],
                    ['actions' => ['index', 'view'], 'allow' => true, 'roles' => ['financial_viewer']],
                    ['allow' => true, 'roles' => ['financial_editor']]
                ]]
        ];
    }

    /**
     * Lists all FinanceInvoice models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FinanceInvoiceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single FinanceInvoice model.
     * $expenditures_return defines what will be the previous page in the breadcrumbs (expenditures or invoices)
     *
     * @param integer $id
     * @param integer $expenditures_return
     * @return mixed
     */
    public function actionView($id, $expenditures_return = 0)
    {
        $invoice_model = $this->findModel($id);

        return $this->render('view', [
            'model' => $invoice_model,
            'expenditures_return' => $expenditures_return
        ]);
    }

    /**
     * Creates a new FinanceInvoice model for the expenditure with id $id.
     * $expenditures_return defines what will be the previous page in the breadcrumbs (expenditures or invoices)
     * If creation is successful, the browser will be redirected to the expendinture 'index' page.
     *
     * @param integer $id
     * @param integer $expenditures_return
     * @return mixed
     */
    public function actionCreate($id, $expenditures_return = 0)
    {
        $invoice_model = new FinanceInvoice();
        $expenditure_model = FinanceExpenditure::findOne(['exp_id' => $id]);
        $supplier_model = FinanceSupplier::findOne(['suppl_id' => $expenditure_model->suppl_id]);
        $invoicetypes_model = FinanceInvoicetype::find()->all();

        $invoice_model->suppl_id = $supplier_model->suppl_id;
        $invoice_model->exp_id = $expenditure_model->exp_id;
        $invoice_model->inv_deleted = 0;

        if ($invoice_model->load(Yii::$app->request->post())) {
            try {
                if (!$invoice_model->save()) {
                    throw new Exception();
                }

                $user = Yii::$app->user->identity->username;
                $year = Yii::$app->session["working_year"];
                Yii::info('User ' . $user . ' working in year ' . $year . ' created invoice with id ' . $id, 'financial');

                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The invoice was created successfully."));
                if ($expenditures_return == 1) {
                    return $this->redirect(['/finance/finance-expenditure']);
                }
                return $this->redirect(['/finance/finance-invoice']);
            } catch (Exception $e) {
                Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failure in creating invoice."));
                if ($expenditures_return == 1) {
                    return $this->redirect(['/finance/finance-expenditure']);
                }
                return $this->redirect(['/finance/finance-invoice']);
            }
        } else {
            return $this->render('create', [
                'invoice_model' => $invoice_model,
                'expenditure_model' => $expenditure_model,
                'supplier_model' => $supplier_model,
                'invoicetypes_model' => $invoicetypes_model,
                'expenditures_return' => $expenditures_return
            ]);
        }
    }

    /**
     * Updates an existing FinanceInvoice model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * $expenditures_return defines what will be the previous page in the breadcrumbs (expenditures or invoices)
     *
     * @param integer $id
     * @param integer $expenditures_return
     * @return mixed
     */
    public function actionUpdate($id, $expenditures_return = 0)
    {
        $invoice_model = $this->findModel($id);
        $expenditure_model = FinanceExpenditure::findOne(['exp_id' => $invoice_model->exp_id]);
        $supplier_model = FinanceSupplier::findOne(['suppl_id' => $expenditure_model->suppl_id]);
        $invoicetypes_model = FinanceInvoicetype::find()->all();

        if ($invoice_model->load(Yii::$app->request->post())) {
            try {
                if (!$invoice_model->save()) {
                    throw new Exception();
                }

                $user = Yii::$app->user->identity->username;
                $year = Yii::$app->session["working_year"];
                Yii::info('User ' . $user . ' working in year ' . $year . ' updated invoice with id ' . $id, 'financial');

                Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The voucher was updated successfully."));
                if ($expenditures_return == 1) {
                    return $this->redirect(['/finance/finance-expenditure']);
                }
                return $this->redirect(['/finance/finance-invoice']);
            } catch (Exception $e) {
                Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failure in udpating voucher."));
                if ($expenditures_return == 1) {
                    return $this->redirect(['/finance/finance-expenditure']);
                }
                return $this->redirect(['/finance/finance-invoice']);
            }
        } else {
            return $this->render('update', [
                'invoice_model' => $invoice_model,
                'expenditure_model' => $expenditure_model,
                'supplier_model' => $supplier_model,
                'invoicetypes_model' => $invoicetypes_model,
                'expenditures_return' => $expenditures_return
            ]);
        }
    }

    /**
     * Deletes an existing FinanceInvoice model.
     * If deletion is successful, the browser will be redirected to the expenditures 'index' page.
     * $expenditures_return defines what will be the previous page in the breadcrumbs (expenditures or invoices)
     *
     * @param integer $id
     * @param integer $expenditures_return
     * @return mixed
     */
    public function actionDelete($id, $expenditures_return = 0)
    {
        try {
            $invoice_model = FinanceInvoice::findOne(['inv_id' => $id]);
            if (is_null($invoice_model)) {
                throw new Exception();
            }
            $statescount = FinanceExpenditurestate::find()->where(['exp_id' => $invoice_model->exp_id])->count();
            if ($statescount > 1) {
                throw new Exception();
            }
            if (!$this->findModel($id)->delete()) {
                throw new Exception();
            }

            $user = Yii::$app->user->identity->username;
            $year = Yii::$app->session["working_year"];
            Yii::info('User ' . $user . ' working in year ' . $year . ' deleted invoice with id ' . $id, 'financial');

            Yii::$app->session->addFlash('success', Module::t('modules/finance/app', "The invoice was deleted succesfully."));
            if ($expenditures_return == 1) {
                return $this->redirect(['/finance/finance-expenditure']);
            }
            return $this->redirect(['/finance/finance-invoice']);
        } catch (Exception $e) {
            Yii::$app->session->addFlash('danger', Module::t('modules/finance/app', "Failure in deleting invoice."));
            if ($expenditures_return == 1) {
                return $this->redirect(['/finance/finance-expenditure']);
            }
            return $this->redirect(['/finance/finance-invoice']);
        }
    }

    /**
     * Finds the FinanceInvoice model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FinanceInvoice the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FinanceInvoice::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
