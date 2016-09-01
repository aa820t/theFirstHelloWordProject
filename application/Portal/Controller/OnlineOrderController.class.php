<?php
/**
 * author:chen yong
 * time:2016-08-30 21:00
 */
namespace Portal\Controller;

use Think\Controller;

/**
 * 在线下单控制器
 */
class OnlineOrderController extends Controller {

    /**
     * 检查验证码是否正确
     * @param $code 提交的验证码
     * @param string $id 第几个验证码
     * @return bool
     */
    private function checkVerifyCode($code, $id = '') {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

    /**
     * 保存 在线下单
     */
    public function saveOrder() {
        /* 获取提交数据 */
        $verifyCode = trim(I('post.verifyCode'));
        $flag = $this->checkVerifyCode($verifyCode);

        if (!$flag) {
            $this->ajaxReturn(array(
                'status' => 'error',
                'msg' => '验证码错误,请重新输入'
            ));
        }

        /* 保存数据 */
        $data = array();

        $data['user_name'] = trim(I('post.userName'));
        $data['phone'] = trim(I('post.phoneNum'));
        $data['address'] = trim(I('post.address'));
        $data['remark'] = trim(I('post.remark'));
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['deal_status'] = '0';

        $return = M('order')->add($data);

        if ($return === false) {
            $data = array(
                'status' => 'error',
                'msg' => '下单失败,请重试'
            );
        } else {
            $data = array(
                'status' => 'success',
                'msg' => '下单成功'
            );
        }

        $this->ajaxReturn($data);
    }

}


