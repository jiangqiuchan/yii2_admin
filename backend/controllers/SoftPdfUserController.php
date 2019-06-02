<?php

namespace backend\controllers;

use Yii;
use backend\models\SoftPdfUser;
use backend\models\SoftPdfUserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\PdfUser;
use common\functions\OrderFunctions;

/**
 * SoftPdfUserController implements the CRUD actions for SoftPdfUser model.
 */
class SoftPdfUserController extends Controller
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
        ];
    }

    /**
     * Lists all SoftPdfUser models.
     * @return mixed
     */
    public function actionIndex()
    {
        //var_dump(Yii::$app->request->queryParams);die;
        $searchModel = new SoftPdfUserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SoftPdfUser model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SoftPdfUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SoftPdfUser();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SoftPdfUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                $psw = Yii::$app->request->post("SoftPdfUser")['new_password'];
                
                $user = PdfUser::findOne($id);
                $user->setPassword($psw);
                $user->save();
                
                $url = Yii::$app->request->referrer;        
                return $this->redirect($url);
            } else {
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            }            
        } else {
            $expireTime = OrderFunctions::getExpireTime($id);
            
            if (!empty($expireTime)) {
                if ($expireTime != 100) {
                    $str = '';
                    if ($expireTime < time()) {
                        $str = '（已过期）';
                    }
                    $expireTime = date("Y-m-d H:i:s",$expireTime).$str;
                } else {
                    $expireTime = '永久';
                }
            }
            
            $model->expire_time = $expireTime;
            
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SoftPdfUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        $url = Yii::$app->request->referrer;

        return $this->redirect($url);
    }

    /**
     * Finds the SoftPdfUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SoftPdfUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SoftPdfUser::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
