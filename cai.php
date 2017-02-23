<?php

/**
* 时时彩
* @author zhaozhuobin
* @date 2016-8-19
*/

class Shishicai
{
	//基本地址
	public $baseUrl;

	//时时彩地址
	public $sscUrl;

	//显示选项
	public $option;

	//警戒值
	public $limit;
	
	//标题
	public $title;

	//微信appid
	public $appId;//wxf0a4c8bd952e9e03

	//微信appsecret
	public $appSecret;//6a90b7b4c3ab177a973adffe528f0853

	//消息接收者
	public $receiver;

	//模板消息ID
	public $templateid;


	public function __construct()
	{
		$this->config();
	}


	//设置基本配置
	public function config()
	{
		$this->baseUrl = 'http://vip0088.so/Lottery';
		$this->sscUrl = '/ssc_list.php';
		$this->title = '时时彩报警精灵';
		$this->appId = 'wxba269a9c96aa8334';
		$this->appSecret = '1736d151abd44e67c29c3b2a9fbcc6ab';
		$this->templateid = 'WHqI1Mob_W_5ryZmjnL4n321K2oRSuN5hW2lfc5f2e8';
		$this->receiver = array(
			'omjVfv0YtDqraESTp0B7gV2278eM-kuEzIwY',
			//'o1gxVt6LfSdlVbTmAEEhlNPnBN-s',
			// 'o1gxVt1q551X2w4914Fy9vltWCWo',
			// 'o1gxVt6qMU6OFIkffQ2jNpY4fDSo',
			// 'o1gxVt_VxAYstCXTGgklwYNBvrbo',
			// 'o1gxVt17OL5uuhOsfyqg99CFI-IE',
		);
		//参数
		$this->option = isset($_GET['t'])?$_GET['t']:1;
		$this->limit = isset($_GET['limit'])?$_GET['limit']:2;
	}

	
	//获取数据
	public function getData()
	{
		$url = $this->baseUrl.$this->sscUrl;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
	    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = preg_match_all('/<img src=\"\/Lottery\/Images\/Ball_2\/(\d).*\"[^>]*>/iu',$result,$match);
		foreach($match[1] as $k=>$v)
		{
			$kk = intval($k/5);
			if($this->option==1){
				$v = $v>4?'大':'小';
			}elseif($this->option==2){
				$v = $v%2==0?'双':'单';
			}
			
			$str = $k%5==0?$v:$str.','.$v;
			$tempdata[$kk][]=$v;
			$data[$kk]=$str;
		}
		$return = array('arr'=>$tempdata,'str'=>$data);
		return $return;
	}


	//机会提醒
	public function warn($tempdata)
	{
		$pos1=$pos2=$pos3=$pos4=$pos5=0;
		$name1 = preg_replace('/<[^>]*>/iu', '', $tempdata[0][0]);
		foreach ($tempdata as $tmk => $tmv) {
			if(isset($tempdata[0][0]) && $tempdata[0][0]==$tempdata[$tmk][0])
			{
				$pos1++;
			}else{
				if(isset($tempdata[0][0]))unset($tempdata[0][0]);
			}
		}
		if($pos1>=$this->limit)
		{
			$this->dealWarn("万位上出现机会，已经连续".$pos1."次".$name1);
		}	
		$name2 = preg_replace('/<[^>]*>/iu', '', $tempdata[0][1]);
		foreach ($tempdata as $tmk => $tmv) {

			if(isset($tempdata[0][1]) && $tempdata[0][1]==$tempdata[$tmk][1])
			{
				$pos2++;
			}else{
				if(isset($tempdata[0][1]))unset($tempdata[0][1]);
			}
		}
		if($pos2>=$this->limit)
		{
			$this->dealWarn("千位上出现机会，已经连续".$pos2."次".$name2);
		}
		$name3 = preg_replace('/<[^>]*>/iu', '', $tempdata[0][2]);
		foreach ($tempdata as $tmk => $tmv) {
			if(isset($tempdata[0][2]) && $tempdata[0][2]==$tempdata[$tmk][2])
			{
				$pos3++;
			}else{
				if(isset($tempdata[0][2]))unset($tempdata[0][2]);
			}
		}
		if($pos3>=$this->limit)
		{
			$this->dealWarn("百位上出现机会，已经连续".$pos3."次".$name3);
		}
		$name4 = preg_replace('/<[^>]*>/iu', '', $tempdata[0][3]);
		foreach ($tempdata as $tmk => $tmv) {
			if(isset($tempdata[0][3]) && $tempdata[0][3]==$tempdata[$tmk][3])
			{
				$pos4++;
			}else{
				if(isset($tempdata[0][3]))unset($tempdata[0][3]);
			}
		}
		if($pos4>=$this->limit)
		{
			$this->dealWarn("十位上出现机会，已经连续".$pos4."次".$name4);
		}
		$name5 = preg_replace('/<[^>]*>/iu', '', $tempdata[0][4]);
		foreach ($tempdata as $tmk => $tmv) {
			if(isset($tempdata[0][4]) && $tempdata[0][4]==$tempdata[$tmk][4])
			{
				$name = $tempdata[0][4];
				$pos5++;
			}else{
				if(isset($tempdata[0][4]))unset($tempdata[0][4]);
			}
		}
		if($pos5>=$this->limit)
		{
			$this->dealWarn("个位上出现机会，已经连续".$pos5."次".$name5);
		}
	}


	//提醒处理
	public function dealWarn($msg)
	{
		$nowHour = date('H',time());
		//提醒时间为早上10点到凌晨2点
		if($nowHour>=8)
		{
			foreach ($this->receiver as $rek => $rev) 
			{
				$this->wechatNews($msg,$rev);
			}
		}
	}


	//微信消息
	public function wechatNews($msg,$openid)
	{
		$token = $this->getWechatToken();
		$data = json_encode(array(
			"touser"=>$openid,
		    	"template_id"=>$this->templateid,
			"url"=>"",
			"topcolor"=>"#FF0000",
		    	"data"=>array(
		    	'shishicai'=>array(
		    		"value"=>$msg.'，时间'.date('H:i:s'),
                		"color"=>"#173177"
		    	),
		    )
		));
		$message = $this->curl('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token,'POST',$data);
		if($message)
		{
			return true;
		}else{
			return false;
		}
	}


	//CURL方法
	public function curl($url,$method='GET',$data=array())
	{
		$ch = curl_init();
		if($method=='GET')
		{
			curl_setopt($ch, CURLOPT_HEADER, 0);
			if(count($data)>0)
			{
				$url.='?'.http_build_query($data);
			}
		}else{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		$output = curl_exec($ch);
		if($output===false)
		{
			echo 'Curl error: ' . curl_error($ch);
		}
		curl_close($ch);
		if($output)
		{
			return json_decode($output,true);
		}else{
			return false;
		}
	}


	//获取微信token
	public function getWechatToken()
	{
		$result = $this->curl('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appId.'&secret='.$this->appSecret);
		if($result)
		{
			return $result['access_token'];
		}else{
			return false;
		}
	}

	//主方法
	public function run()
	{
		if($this->option ==3)
		{
			$data = $this->getData();
			foreach($data['arr'] as $key=>$value)
			{
				echo '<h1'.($key==0?' style="color:red;"':'').'>第'.date('Ymd-',time()).'-'.(count($data['arr'])-$key-1).'期开奖结果:'.$data['str'][$key].'</h1>';	
			}
		}elseif($this->option == 4){
			echo '<h1>敬请期待</h1>';		
		}elseif($this->option == 5){
			echo '<h1>没错，就是我!</h1>';		
		}
		else{
			$data = $this->getData();
			$this->warn($data['arr']);
		}
	}

}	

//实例化并执行
$shishicaiClass = new Shishicai();
$shishicaiClass->run();
