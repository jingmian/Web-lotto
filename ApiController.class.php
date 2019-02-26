<?php
namespace Admin\Controller;
use Think\Controller;
header("Access-Control-Allow-Origin: *");
class ApiController extends Controller{
	//进行用户信息的录入
	public function userAddress(){
		$data = I("get.");
		$map['openid']=$data['openid'];
		$stauts = M("lottouser")->where($map)->find();
		$data["id"]= $stauts["id"];
	    M("lottouser")->save($data);
	}

	public function userinfo(){
		$data = I("get.");
		$map['openid']=$data['openid'];
		$stauts = M("lottouser")->where($map)->find();
		//如果不存在才添加
		if (!$stauts) {
				M("lottouser")->add($data);
		}
	}

	public function data(){
		//回显前台数据
		$pros =  D("prize")->select();
		$proInfo = array();
		foreach ($pros as $pro) {
			$proInfo[] = $pro['level'].":".$pro['name'];
		}
		echo json_encode($proInfo);
	}

	public function getNum(){
		$openid = I("get.openid");
		//通过id进行查询用户得得奖信息，如果已经获奖，直接返回，否则，经过下面算法计算得出
		$map["openid"]=$openid ;
		$userinfo = M("lottouser")->where( $map )->find();
		//自创抽奖算法（业务需要）
		//第一步：取出所有奖品的信息
		$pros =  D("prize")->select();
		if (!empty($userinfo["prize"])) {
			//说明当前用户已经中过奖，直接返回谢谢惠顾的id
				echo json_encode(count($pros));
		}else{
			//第二步：构造奖品的前台显示信息和概率的计算方法，
			//定义一个空数组,构造自己所需要的数据，此时需要注意php版本，如果$gifts = []；可能会不支持
			//因为前台需要奖品的名称，奖品的等级，后台需要计算中奖概率 和奖品的数量控制，因此我构造的数据 如下
			//并且以相应的id作为新数组的下标
			$gifts = array();
			foreach ($pros as $pro) {
				if ($pro['num']!=0) {
					//当前物品的数量不为0，才进行循环，否则就将其从数组中剔除，数量都没有了，抽到还有用吗？？
					$gifts[ $pro['prize_id'] ] = array('id'=>$pro['prize_id'],"name"=>$pro['name'],"level"=>$pro['level'],"cent"=>$pro['cent'],"num"=>$pro['num']);
				}
			}
			// dump($gifts );
			// $gifts = [
			// 		1 => array('id'=>1,'name'=>小米路由器,"levle"=>四等奖,'cent'=>30,num=>20),
			// 		2 => array('id'=>2,'name'=>小米手机,'cent'=>0.5),
			// 		3 => array('id'=>3,'name'=>小米电视机,'cent'=>5),
			// 	 	4 => array('id'=>4,'name'=>小米平衡车,'cent'=>10),
			// 		5 => array('id'=>5,'name'=>小米笔记本PRO,'cent'=>10),
			// 		6 => array('id'=>6,'name'=>小米彩虹电池,'cent'=>74),
			// 	 ];
			//第三步：构造后台抽奖的数据
			foreach ($gifts as $gift ) {
				$gift_cents[ $gift['id'] ] = $gift['cent'];
			}
			// dump($gift_cents);die;

			 //第五步，调用抽奖处理程序$res即是key
			$res = $this->randGift( $gift_cents );
			//进行数量的控制
			$condition['num'] = intval($gifts[$res]["num"])-1;//数量减1
			if ($condition['num'] >= 0) {
				D("prize")->where( "prize_id=".$res) ->save( $condition );
			}
			$data=$gifts[$res];
			if (!empty($data)) {
					//将用户获得的奖品进行入库,先获取奖品的名称
					$term["prize_id"]=$data["id"];
					$prize = D("prize")->where($term)->find();
					// echo json_encode($userinfo["id"]);exit;
					//将奖品的名称进行入库，可视化显示
					$con["prize"] = $prize["level"].$prize["name"];
					//进行奖品的 入库操作
					M("lottouser")->where("id=".$userinfo["id"])->save($con);
					echo  json_encode($data["id"]); //前台请求过去的数据
					exit;
			}else{
				//说明所有商品的库存数量为0，直接返回谢谢参与的Id
				echo json_encode(count($pros));
			}
		}
	}

		// 	根据后台进行算法计算
		// 	$pros =  D("prize")->select();
		// 	江亮2018/4/18日晚
		// 	礼品数组如下
		// 	id表示礼品的id
		// 	cent表示中奖的几率
		// 	中奖的最高的几率是100, iphone的中奖几率是0.01%,谢谢惠顾的中奖几率是54%
		// 	$gifts = [];
		// 	$gifts = array();
		// 	foreach ($pros as $pro) {
		// 		$gifts[] = array('id'=>$pro['prize_id'],"name"=>$pro['name'],"level"=>$pro['level'],'cent'=>$pro['cent']);
		// 	}
		// 	相当于如下
		// 	 $gifts = [
		// 	 	0 => array('id'=>1,'name'=>小米路由器,"levle"=>四等奖,'cent'=>0.5),
		// 			1 => array('id'=>2,'name'=>小米手机,'cent'=>0.5),
		// 			2 => array('id'=>3,'name'=>小米电视机,'cent'=>5),
		// 			3 => array('id'=>4,'name'=>小米平衡车,'cent'=>10),
		// 			4 => array('id'=>5,'name'=>小米笔记本PRO,'cent'=>10),
		// 			5 => array('id'=>6,'name'=>小米彩虹电池,'cent'=>74),
		// 	];
		// //获取所有的礼品对应的中奖几率
		// foreach ($gifts as $g ) {
		// 	$gift_cents[ $g['id'] ] = $g['cent'];
		// }
		// // dump($gift_cents);die;
		// //调用中奖程序
		// //中奖的level
		// $res = $this->randGift( $gift_cents );
		// $data['level']=$res;
		// $data['name']=$gifts[$res-1]['name'];
		// echo json_encode($data) ; //前台请求过去的数据
		// exit;
		// }
		// 第四步：构造处理数据的方法
		 function randGift( $gift_cents ){
			$res = ''; //表示中奖奖品的结果
			$centTotal = array_sum($gift_cents); //中奖的最高概率的权重
			foreach ($gift_cents as $key => $current_cent) {
				//$key表示礼品的level, $current_cent表示当前礼品的概率
				$rand = mt_rand(1,$centTotal);//求1-100的范围
				if( $rand <= $current_cent ){
					$res = $key; //小于或等于54以下都代表已经中奖了。$key也就是当前的id
					break; //停止抽奖
				}else{
					//比如当前的中奖概率55 ，我们我们下次的随机数范围就是（1，45）
					$centTotal -= $current_cent;
				}
			}
			unset( $gift_cents ); //删除概率，防止二次抽奖时错误
			return $res;
		 }
}