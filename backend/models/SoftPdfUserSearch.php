<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\SoftPdfUser;
use backend\components\commonality;

/**
 * SoftPdfUserSearch represents the model behind the search form about `backend\models\SoftPdfUser`.
 */
class SoftPdfUserSearch extends SoftPdfUser
{
    public $expire_time;
    public $recharges;
    public $is_vip;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'last_login_at', 'updated_at','recharges','is_vip' /* 'expire_time' */], 'integer'],
            [['mobile', 'auth_key', 'password_hash', 'password_reset_token','created_at'], 'safe'],
            [['id','mobile','username','recharges','channel'], 'trim'],
            [['channel'], 'string'],
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
        $query = SoftPdfUser::find()
            ->select("soft_pdf_user.*,a.expire_time,a.recharges")
            ->leftJoin("(SELECT user_id, MAX(expire_time) AS expire_time,count(user_id) AS recharges FROM soft_pdf_order WHERE pay_status=1 GROUP BY user_id ) AS a","a.user_id = soft_pdf_user.id");

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => '15',
                //'route' => 'product-details/index'
            ],
            'sort' => [
                'attributes' => ['created_at','id','askreply_num','expire_time','recharges'],
                'defaultOrder' => ['id' => SORT_DESC]
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'soft_pdf_user.id' => $this->id,
//            'created_at' => $this->created_at,
            'last_login_at' => $this->last_login_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'username', commonality::userTextEncode($this->username)])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'channel', $this->channel]);
        
//        if ((!empty($params['start_at1']) && !empty($params['end_at1'])) || (empty($params['start_at1']) && !empty($params['end_at1']))) {
//            $startTime = empty($params['start_at1']) ? 0 : strtotime($params['start_at1']);
//            $endTime = strtotime($params['end_at1']) + 86399;
//            $query->andFilterWhere(['between', 'soft_pdf_user.created_at', $startTime, $endTime]);
//        }

        if (!empty($params['SoftPdfUserSearch']['created_at'])) {
            $dateArr = explode('到',$params['SoftPdfUserSearch']['created_at']);
            if ($dateArr) {
                $startTime = strtotime($dateArr[0]);
                $endTime = strtotime($dateArr[1]) + 86399;
                $query->andFilterWhere(['between', 'soft_pdf_user.created_at', $startTime, $endTime]);
            }
        }
        
        if (!empty($params['expire_time'])) {
            $endTime = strtotime($params['expire_time']) + 86399;
            $query->andFilterWhere(['between', 'expire_time', 0, $endTime]);
        }
        
        if (isset($params['module_type'])) {
            if ($params['module_type'] != 1) {
                switch ($params['module_type'])
                {
                    case 2:
                        //昨日在线
                        $timeon = strtotime('-1 day',strtotime(date('Y-m-d')));
                        $timeend = $timeon + 86399;
                        $where = "login_date BETWEEN $timeon AND $timeend";
                        $query->rightJoin("(SELECT * FROM pdf_login_log WHERE $where) AS l","l.user_id = soft_pdf_user.id")->groupBy('soft_pdf_user.id');
                        break;
                    case 3:
                        //一周在线
                        $timeon = strtotime('-7 day',strtotime(date('Y-m-d')));
                        $timeend = strtotime(date('Y-m-d')) - 1;
                        $where = "login_date BETWEEN $timeon AND $timeend";
                        $query->rightJoin("(SELECT * FROM pdf_login_log WHERE $where) AS l","l.user_id = soft_pdf_user.id")->groupBy('soft_pdf_user.id');
                        break;
                    case 4:
                        //一月在线
                        $timeon = strtotime('-1 month',strtotime(date('Y-m-d'))) - 86400;
                        $timeend = strtotime(date('Y-m-d')) - 1;
                        $where = "login_date BETWEEN $timeon AND $timeend";
                        $query->rightJoin("(SELECT * FROM pdf_login_log WHERE $where) AS l","l.user_id = soft_pdf_user.id")->groupBy('soft_pdf_user.id');
                        break;
                    case 5:
                        //今日注册并充值
                        $timeon = strtotime(date('Y-m-d',time()));
                        $timeend = $timeon + 86399;
                        $where = "created_at BETWEEN $timeon AND $timeend AND recharges >= 1";
                        $query->where($where);
                        break;
                    default:
                        $where = '';
                }
            }           
        }
        
        //充值次数查询
        if (isset($params['SoftPdfUserSearch']['recharges']) && $params['SoftPdfUserSearch']['recharges'] === '0') {
            $query->andWhere("recharges IS NULL");
        } else {
            $query->andFilterWhere(['recharges' => $this->recharges]);
        }

        if (!empty($params['SoftPdfUserSearch']['is_vip'])) {
            switch ($params['SoftPdfUserSearch']['is_vip'])
            {
                case 1:
                    //会员
                    $query->andWhere("recharges IS NOT NULL");
                    break;
                case 2:
                    //非会员
                    $query->andWhere("recharges IS NULL");
                    break;
                default:
            }
        }
        
//                        print_r($query->createCommand()->getRawSql());die;
        return $dataProvider;
    }
    
    
}
