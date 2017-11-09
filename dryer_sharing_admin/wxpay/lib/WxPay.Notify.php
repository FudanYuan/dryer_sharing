<?php
require_once '../../common/database/system.class.inc.php';
require_once '../../common/database/dbInfo.php';

class WxPayNotify extends WxPayNotifyReply
{
    /**
     *
     * 回调入口
     * @param bool $needSign 是否需要签名输出
     */
    final public function Handle($needSign = true)
    {
        $msg = "OK";
        //当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
        $return = WxpayApi::notify(array($this, 'NotifyCallBack'), $msg);
        if ($return == false) {
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
            $this->ReplyNotify(false);
            return;
        } else {
            //该分支在成功回调到NotifyCallBack方法，处理完成之后流程
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");

            //获取通知的数据
            $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
            Log::DEBUG($xml);

            // 获取设备号、用户id及充值金额
            $arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

            // 获取商户名称，商户编号
            $info = explode('_', $arr['attach']);
            $product_id = $info[0];
            $time = $info[1];
            $open_id = $arr['openid'];
            $fee = $arr['total_fee'] / 100;

            // 连接数据库
            $dbInfo = new dbInfo();
            $connDB = new ConnDB($dbInfo::DBSTYLE, $dbInfo::HOST,
                $dbInfo::USER, $dbInfo::PWD, $dbInfo::DBNAME);
            $conn = $connDB->GetConnld();
            $adminDB = new AdminDB();

            // 获得设备序号
            $sql = "select dev_id from device_info where mac_addr = '{$product_id}'";
            $result = $adminDB->ExecSQL($sql, $conn);
            $dev_id = $result[0]['dev_id'];
            $now = date('Y-m-d H:i:s', time());

            // 更新数据库，事务操作
            $sql = array();
            $sql[0] = "insert into consume_rec (dev_id, user_id, consume_t, cost, time_len) values('{$dev_id}', '{$open_id}', '{$now}', {$fee}, {$time})";
            $sql[1] = "update device_info set balance = {$time}, status = 1 where dev_id = '{$dev_id}'";
            $adminDB->Transcation($sql, $conn);
        }

        $this->ReplyNotify($needSign);
    }

    /**
     *
     * 回调方法入口，子类可重写该方法
     * 注意：
     * 1、微信回调超时时间为2s，建议用户使用异步处理流程，确认成功之后立刻回复微信服务器
     * 2、微信服务器在调用失败或者接到回包为非确认包的时候，会发起重试，需确保你的回调是可以重入
     * @param array $data 回调解释出的参数
     * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    public function NotifyProcess($data, &$msg)
    {
        //TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
        return true;
    }

    /**
     *
     * notify回调方法，该方法中需要赋值需要输出的参数,不可重写
     * @param array $data
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    final public function NotifyCallBack($data)
    {
        $msg = "OK";
        $result = $this->NotifyProcess($data, $msg);

        if ($result == true) {
            $this->SetReturn_code("SUCCESS");
            $this->SetReturn_msg("OK");
        } else {
            $this->SetReturn_code("FAIL");
            $this->SetReturn_msg($msg);
        }
        return $result;
    }

    /**
     *
     * 回复通知
     * @param bool $needSign 是否需要签名输出
     */
    final private function ReplyNotify($needSign = true)
    {
        //如果需要签名
        if ($needSign == true &&
            $this->GetReturn_code() == "SUCCESS"
        ) {
            $this->SetSign();
        }
        WxpayApi::replyNotify($this->ToXml());
    }

    /**
     *
     * 申请退款
     */
    final private function Refund($out_trade_no, $total_fee, $refund_fee)
    {
        $input = new WxPayRefund();
        $input->SetOut_trade_no($out_trade_no);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(WxPayConfig::MCHID . date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);
        WxPayApi::refund($input);
    }
}
