<?php

namespace backend\controllers;

use Yii;
use backend\models\SoftPdfConvertNew;
use backend\models\YdqPointSearch;
use backend\models\ZhqPointSearch;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\models\Channel;

/**
 * SoftPdfController implements the CRUD actions for SoftPdf model.
 */
class SoftPdfController extends Controller
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

    //转换器实时数据页
    public function actionZhqPointPage()
    {
        $searchModel = new ZhqPointSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('zhq-point-page', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
