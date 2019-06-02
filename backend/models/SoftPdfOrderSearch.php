<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\SoftPdfOrder;
use backend\components\commonality;

/**
 * SoftPdfOrderSearch represents the model behind the search form about `backend\models\SoftPdfOrder`.
 */
class SoftPdfOrderSearch extends SoftPdfOrder
{
    public $mobile;
    public $username;
    public $channel;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'package', 'pay_status', 'start_time', 'expire_time', 'notify_at', 'updated_at','gmt_payment','recharges'], 'integer'],
            [['pay_type', 'out_trade_no', 'trade_no','mobile','created_at','referer'], 'safe'],
            [['money', 'receipt_amount'], 'number'],
            [['username','user_id','gmt_payment','money','trade_no','recharges','channel'],'trim']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SoftPdfOrder::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '15',
                //'route' => 'product-details/index'
            ],
            'sort' => [
                'attributes' => ['created_at','user_id','gmt_payment','money','recharges','updated_at'],
                'defaultOrder' => [
                    'updated_at' => SORT_DESC,
                ]
            ],
        ]);
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if(isset($params['SoftPdfOrderSearch'])) {
            $query = $query->select("soft_pdf_order.*,soft_pdf_user.mobile,a.recharges,soft_pdf_user.channel")
                ->joinWith('pdfUser')
                ->leftJoin("(SELECT user_id,count(user_id) AS recharges FROM soft_pdf_order WHERE pay_status = '1' GROUP BY user_id ) AS a","a.user_id = soft_pdf_user.id");
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'soft_pdf_order.user_id' => $this->user_id,
            'money' => $this->money,
            'receipt_amount' => $this->receipt_amount,
            'package' => $this->package,
            'pay_status' => 1,
            'start_time' => $this->start_time,
            'expire_time' => $this->expire_time,
            'notify_at' => $this->notify_at,
            'updated_at' => $this->updated_at,
            'recharges' => $this->recharges,
        ]);

        $query->andFilterWhere(['like', 'pay_type', $this->pay_type])
            ->andFilterWhere(['like', 'out_trade_no', $this->out_trade_no])
            ->andFilterWhere(['like', 'trade_no', $this->trade_no])
            ->andFilterWhere(['like', 'username', commonality::userTextEncode($this->username)])
            ->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'channel', $this->channel]);
        
//        if ((!empty($params['start_at1']) && !empty($params['end_at1'])) || (empty($params['start_at1']) && !empty($params['end_at1']))) {
//            $startTime = empty($params['start_at1']) ? 0 : strtotime($params['start_at1']);
//            $endTime = strtotime($params['end_at1']) + 86399;
//            $query->andFilterWhere(['between', 'soft_pdf_order.created_at', $startTime, $endTime]);
//        }
        
        if (!empty($params['gmt_payment'])) {
            $endTime = strtotime($params['gmt_payment']) + 86399;
            $query->andFilterWhere(['between', 'gmt_payment', 0, $endTime]);
        }

        if (!empty($params['SoftPdfOrderSearch']['created_at'])) {
//            $startTime = strtotime($params['SoftPdfOrderSearch']['created_at']);
//            $endTime = $startTime + 86399;
            $dateArr = explode('到',$params['SoftPdfOrderSearch']['created_at']);
            if ($dateArr) {
                $startTime = strtotime($dateArr[0]);
                $endTime = strtotime($dateArr[1]) + 86399;
                $query->andFilterWhere(['between', 'soft_pdf_order.created_at', $startTime, $endTime]);
            }
        }
        
        if (!empty($params['SoftPdfOrderSearch']['referer'])) {
            $referer = $params['SoftPdfOrderSearch']['referer'];
            if ($referer == 'pdf') {
                $query->andFilterWhere(['like', 'soft_pdf_order.referer', $referer])
                    ->andWhere("package <> 11");
            } else {
                $query->andFilterWhere(['like', 'soft_pdf_order.referer', $referer]);
            };
        }
        
        if (isset($params['module_type'])) {
            switch ($params['module_type'])
            {
                case 6:
                    $timeon = strtotime(date('Y-m-d',time()));
                    $timeend = $timeon + 86399;
                    $where = "soft_pdf_order.created_at BETWEEN $timeon AND $timeend";
                    break;
                default:
                    $where = '';
            }
        
            $query->andWhere("$where");
        }
        
//                                print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
}
