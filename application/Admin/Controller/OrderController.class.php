<?php

/**
 * Order(下单管理)
 */
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class OrderController extends AdminbaseController {

    /* 首页 */
    public function index() {
        $deal_status = I('deal_status', '0');
        $formget = array();
        $formget['start_time'] = I('start_time');
        $formget['end_time'] = I('end_time');
        $formget['keyword'] = I('keyword');

        $where = '1=1';

        if ($deal_status != '') {
            $where .= ' AND deal_status=' . $deal_status;
        }

        if ($formget['start_time'] != '') {
            $where .= ' AND create_time >=\'' . $formget['start_time'] . ' 00:00:00\'';
        }
        if ($formget['end_time'] != '') {
            $where .= ' AND create_time <=\'' . $formget['end_time'] . ' 23:59:59\'';
        }

        if ($formget['keyword'] != '') {
            $where .= ' AND (user_name LIKE \'%' . $formget['keyword'] . '%\' OR address LIKE \'%' . $formget['keyword'] . '%\' OR remark LIKE \'%' . $formget['keyword'] . '%\' OR phone LIKE \'%' . $formget['keyword'] . '%\')';
        }

        $M_order = M('order');

        $count = $M_order
            ->where($where)
            ->count();

        $page = $this->page($count, 20);

        $orders = $M_order
            ->where($where)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('create_time DESC')
            ->select();

        $this->assign("Page", $page->show('Admin'));
        $this->assign("orders", $orders);
        $this->assign("deal_status", $deal_status);
        $this->assign("formget", $formget);

        $this->display();
    }

    /**
     * 调整状态
     */
    public function changeStatus() {

        $data["deal_status"] = $_GET['deal_status'];

        $text = '状态设置为';
        switch ($_GET['deal_status']) {
            case '0':
                $text .= '未处理';
                break;
            case '1':
                $text .= '已处理';
                break;
            case '2':
                $text .= '已取消';
                break;
        }

        $ids = join(",", $_POST['ids']);

        if (M('order')->where("id in ($ids)")->save($data) !== false) {
            $this->success($text . "成功！");
        } else {
            $this->error($text . "审核失败！");
        }

    }


    private function SS($name, $value = '', $expire = 0) {
        $filename = C('DATA_CACHE_PATH') . md5($name) . '.php';

        if ($value === '') {
            //---- 获取缓存 ----
            if (!is_file($filename)) {
                return false;
            }
            $content = file_get_contents($filename);
            if (false !== $content) {
                $expire = (int)substr($content, 8, 12);
                if ($expire != 0 && time() > filemtime($filename) + $expire) {
                    //缓存过期删除缓存文件
                    unlink($filename);
                    return false;
                }
                //---- 分割字符串 ----
                $content = substr($content, 20, -3);

                //---- 序列化数据 ----
                $content = unserialize($content);
                return $content;
            } else {
                return false;
            }
        } else {
            //---- 缓存数据 ----
            $data = serialize($value);
            $data = "<?php\n//" . sprintf('%012d', $expire) . $data . "\n?>";
            $result = file_put_contents($filename, $data);
            if ($result) {
                clearstatcache();
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * ajax 获取当前等待处理的在线订单的数量
     */
    public function getWaitDealOnlineOrderNum() {
        /* 预读缓存的在线订单的数量 */
        $count = S('needDealOnlineOrderNum');

        if ($count === false) {
            $count = M('order')
                ->where(array(
                    'deal_status' => '0'
                ))
                ->count();
            $count = intval($count);

            /* 缓存数据 */
            S('needDealOnlineOrderNum',$count);
        }
        
        $this->ajaxReturn($count);
    }

    /**
     * ajax 获取当前等待处理的在线订单的列表
     */
    public function getWaitDealOnlineOrderList() {
        $list= M('order')
            ->where(array(
                'deal_status' => '0'
            ))
            ->order('create_time DESC')
            ->select();
        echo json_encode($list);
        exit;
    }
}