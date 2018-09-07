<?php

namespace app\modules\schooltransport\controllers;

use Exception;
use Yii;
use app\modules\schooltransport\Module;
use app\modules\schooltransport\models\SchtransportProgram;
use app\modules\schooltransport\models\SchtransportProgramSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * SchtransportProgramController implements the CRUD actions for SchtransportProgram model.
 */
class SchtransportProgramController extends Controller
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
                    ['actions' => ['index', 'view'], 'allow' => true, 'roles' => ['schtransport_viewer']],
                    ['actions' => [ 'update', 'delete'], 'allow' => true, 'roles' => ['schtransport_editor']],
                    ['allow' => true, 'roles' => ['schtransport_director']]
                ]
            ]
        ];
    }

    /**
     * Lists all SchtransportProgram models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SchtransportProgramSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SchtransportProgram model.
     * @param integer $id
     * @return mixed
     */
    /*
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $programcateg_model = $model->findOne(['programcategory_id' => $model->programcategory_id]);
        return $this->render('view', [
            'model' => $model,
            'programcateg_model' => $programcateg_model
        ]);
    }*/

    /**
     * Creates a new SchtransportProgram model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    /*
    public function actionCreate()
    {
        $model = new SchtransportProgram();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->program_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }*/

    /**
     * Updates an existing SchtransportProgram model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        try {
            $model = $this->findModel($id);
            
            if ($model->load(Yii::$app->request->post())) {
                if (!$model->save()) {
                    throw new Exception("The update of the program details failed.");
                }
                Yii::$app->session->addFlash('success', Module::t('modules/schooltransport/app', 'The program details were updated successfully.'));
                return $this->redirect(['index', 'id' => $model->program_id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } catch (Exception $exc) {
            Yii::$app->session->addFlash('danger', Module::t('modules/schooltransport/app', $exc->getMessage()));
            return $this->redirect(['index']);
        }
    }

    /**
     * Deletes an existing SchtransportProgram model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        try {
            if (!$this->findModel($id)->delete()) {
                throw new Exception("The program cannot be deleted. Check if there are transports assigned to it.");
            }
            
            Yii::$app->session->addFlash('success', Module::t('modules/schooltransport/app', 'The program was deleted successfully'));
            return $this->redirect(['index']);
        } catch (Exception $exc) {
            Yii::$app->session->addFlash('danger', Module::t('modules/schooltransport/app', $exc->getMessage()));
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the SchtransportProgram model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SchtransportProgram the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SchtransportProgram::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
