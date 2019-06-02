<?php

namespace backend\controllers;

use Yii;
use backend\models\SoftPdfOrder;
use backend\models\SoftPdfOrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\SoftPdfRefund;
use common\models\ErrorLog;
use common\components\PayFun;

require_once "../../vendor/WxPay/WxPay.Api.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/log.php";

/**
 * SoftPdfOrderController implements the CRUD actions for SoftPdfOrder model.
 */
class SoftPdfOrderController extends Controller
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
     * Lists all SoftPdfOrder models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SoftPdfOrderSearch();
        $params = Yii::$app->request->queryParams;
//        $params['SoftPdfOrderSearch']['pay_status'] = 1;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SoftPdfOrder model.
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
     * Creates a new SoftPdfOrder model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SoftPdfOrder();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SoftPdfOrder model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SoftPdfOrder model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the SoftPdfOrder model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SoftPdfOrder the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SoftPdfOrder::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    //退款
    public function actionRefundAct()
    {
        $psw = Yii::$app->request->post('psw');
        $id = Yii::$app->request->post('id');
        $type = Yii::$app->request->post('type');
        $fee = Yii::$app->request->post('fee');
        $data = ['status' => '0','msg' => '未知错误'];

        $refundPsw = yii::$app->params['refundPsw'];
        if($psw && $id && $type && floatval($fee) && ($refundPsw == $psw)) {
            $orderM = SoftPdfOrder::findOne($id);
            $errorM = new ErrorLog();
    
            if(isset($orderM->trade_no) && $orderM->trade_no != ""){
                //初始化日志
                $logHandler= new \CLogFileHandler("../web/logs/".date('Y-m-d').'.log');
                $log = \Log::Init($logHandler, 15);
                
                $total_fee = $orderM->money;
                $now_fee = $orderM->receipt_amount;
                $out_trade_no = $orderM->out_trade_no;
                $out_request_no = date('Ymd').time().rand(10000,99999);
                $transaction_id = $orderM->trade_no;

                if ($now_fee < $fee) {
                    $data = ['status' => '0','msg' => '退款金额超出订单剩余金额'];
                    return json_encode($data);
                } 
                
                if ($total_fee == $fee) {
                    $orderPayStatus = '9';
                    $refundType = '1';
                    $reason = '套餐全额协商退款';
                } else {
                    $refundType = '2';
                    $orderPayStatus = '10';   
                    $reason = '套餐部分协商退款';
                }
                
                //退款表
                $refundM = new SoftPdfRefund();
                $refundM->order_id = $id;
                $refundM->admin_id = Yii::$app->user->id;
                $refundM->user_id = $orderM->user_id;
                $refundM->money = $fee;
                $refundM->batch_no = date('Ymd').time().rand(10000,99999);
                $refundM->batch_num = 1;
                $refundM->type = $refundType;
                $refundM->reason = $transaction_id.'^'.$orderM->receipt_amount.'^'.$reason;
                $refundM->created_at = time();
                $refundM->save();

                if ($type == 'weixin') {
                    $result = PayFun::WxRefund($transaction_id, $total_fee, $fee);
                    $data = $this->WxRefundRes($result,$orderPayStatus,$refundType,$fee,$orderM,$refundM);
                } elseif ($type == 'alipay') {
                    $result = PayFun::AliRefundNew($out_trade_no,$transaction_id,$total_fee,$fee,$reason,$out_request_no);
                    $data = $this->AliRefundRes($result,$orderPayStatus,$refundType,$fee,$orderM,$refundM);
                }
                \Log::DEBUG("call back:" . json_encode($result));
                
                return json_encode($data);
            }

            return json_encode($data);
        } else {
            $data = ['status' => '0','msg' => '密码或参数错误'];
            return json_encode($data);
        }
    }
    
    //微信退款
    public static function WxRefundRes($result,$orderPayStatus,$refundType,$refund_fee,$orderM,$refundM)
    {
        if(($result['return_code']=='SUCCESS') && ($result['result_code']=='SUCCESS')){
            $transaction = Yii::$app->db->beginTransaction();
            try {
                //退款成功
                $orderM->pay_status = $orderPayStatus;
                $orderM->receipt_amount = $orderM->receipt_amount - $refund_fee;
                
                if (!$orderM->receipt_amount) {
                    $orderM->pay_status = 9;
                }
                
                $orderM->save();
                $refundM->state = 1;
                if (!$refundM->save()) {
                    $transaction->rollBack();
                };
                
                $transaction->commit();

                $data = ['status' => '1','msg' => '退款成功'];
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else if(($result['return_code']=='FAIL') || ($result['result_code']=='FAIL')){
            $reason = (empty($result['err_code_des'])?$result['return_msg']:$result['err_code_des']);
            ErrorLog::logError(json_encode($result));
            $data = ['status' => '0','msg' => '退款失败 '.$reason];
        }else{
            //失败
            ErrorLog::logError(json_encode($result));
            $data = ['status' => '0','msg' => '退款失败'];
        }
        
        return $data;
    }
    
    //支付宝退款
    public static function AliRefundRes($result,$orderPayStatus,$refundType,$refund_fee,$orderM,$refundM)
    {
        if(($result->msg =='Success') && ($result->code =='10000')){
            $transaction = Yii::$app->db->beginTransaction();
            try {
                //退款成功
                $orderM->pay_status = $orderPayStatus;
                $orderM->receipt_amount = $orderM->receipt_amount - $refund_fee;
                
                if (!$orderM->receipt_amount) {
                    $orderM->pay_status = 9;
                }

                $orderM->save();
                $refundM->state = 1;
                if (!$refundM->save()) {
                    $transaction->rollBack();
                };
                
                $transaction->commit();

                $data = ['status' => '1','msg' => '退款成功'];
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else if (($result->msg =='Business Failed') || ($result->code =='40004')){
            $reason = $result->sub_msg;
            ErrorLog::logError(json_encode($result));
            $data = ['status' => '0','msg' => '退款失败: '.$reason];                            
        } else{
            //失败
            ErrorLog::logError(json_encode($result));
            $data = ['status' => '0','msg' => '退款失败'];
        }
        
        return $data;
    }
    
    //计算当前查询语句总金额
    public function actionTotalMoney()
    {
        $query = Yii::$app->request->get('query','');
        if ($query) {
            $query2 = str_replace(substr($query,0,strpos($query, 'FROM')),"SELECT SUM(money) money ",$query);
            $res = Yii::$app->db->createCommand($query2)->queryColumn();
            
            $data = ['status' => '1','msg' => $res['0']];
        } else {
            $data = ['status' => '0','msg' => '获取金额出错'];
        }
        return json_encode($data);
    }
    
}
