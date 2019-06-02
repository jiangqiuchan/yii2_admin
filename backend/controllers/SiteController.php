<?php
namespace backend\controllers;

use backend\models\AdminCreate;
use backend\models\SoftPdfOrder;
use backend\models\SoftPdfUser;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'reset-password'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['get'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $data = [];

        //总注册
        $total_register = SoftPdfUser::getUserRegData();
        $data['total_register'] = $total_register;

        //今日注册
        $data['today_register'] = SoftPdfUser::getUserRegData(time());

        //总充值人数
        $data['total_user_order'] = SoftPdfOrder::getOrderData($time='',$type="user")['user_order_num'];

        //今日注册并充值
        $data['today_register_order'] = SoftPdfUser::getUserOrderData(time());

        //订单总数/订单总额/平均订单金额
        $data['total_order'] = SoftPdfOrder::getOrderData()['order_num'];
        $data['total_order_amount'] = SoftPdfOrder::getOrderData()['order_amount'];
        $data['total_order_amount_avg'] = SoftPdfOrder::getOrderData()['order_num'] ? round(SoftPdfOrder::getOrderData()['order_amount']/SoftPdfOrder::getOrderData()['order_num'],2) : 0;

        //今日订单数/今日订单总额/今日平均订单金额
        $data['today_order'] = SoftPdfOrder::getOrderData(time())['order_num'];
        $data['today_order_amount'] = SoftPdfOrder::getOrderData(time())['order_amount'];
        $data['today_order_amount_avg'] = SoftPdfOrder::getOrderData(time())['order_num'] ? round(SoftPdfOrder::getOrderData(time())['order_amount']/SoftPdfOrder::getOrderData(time())['order_num'],2) : 0;

        $today = date('Y-m-d',time()).'到'.date('Y-m-d',time());
        return $this->render('index', [
            'data' => $data,
            'today' => $today
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel(AdminCreate::className(),$id);
        if ($model->load(Yii::$app->request->post()) && $model->saveData()) {
            return $this->redirect(['index']);
        } else {
            if(isset($_GET['type']) && $_GET['type'] == "ajax") {
                return $this->renderAjax('update', [
                    'model' => $model,
                ]);
            }else{
                return $this->render('reset-password', [
                    'model' => $model,
                ]);
            }
        }
    }

    protected function findModel($form,$id)
    {
        if (($model = $form::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
