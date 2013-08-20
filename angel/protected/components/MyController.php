<?php

class MyController extends Controller
{
	/**
	 * ajax方式返回数据到客户端
	 * @param mixed $data 要返回的数据
	 * @param string $info 提示信息
	 * @param integer $status 状态码
	 * @param string $type 返回数据的类型
	 * @param boolean $exit 是否执行后直接exit
	 * @return void
	 */
	protected function ajaxReturn($data, $info = '', $status = 1, $type = 'JSON', $exit = TRUE)
	{
		$result           = array();
		$result['data']   = $data;
		$result['info']   = $info;
		$result['status'] = intval($status);
		$type             = strtoupper($type);

		if ($type == 'JSON')
		{
			header('content-type:text/html; charset=utf-8');
			echo json_encode($result);
		}
		else
		{
			header('content-type:text/html; charset=utf-8');
			echo $data;
		}

		if ($exit)
		{
			exit();
		}
	}

	/**
	 * ajax操作成功快捷方法
	 * @param string $msg 提示信息
	 * @param mixed $data 要返回的数据
	 * @param boolean $exit 是否执行后直接exit
	 * @return void
	 */
	protected function success($msg, $data = NULL, $exit = TRUE)
	{
		$this->ajaxReturn($data, $msg, 1, $exit);
	}

	/**
	 * ajax操作失败快捷方法
	 * @param string $msg 提示信息
	 * @param mixed $data 要返回的数据
	 * @param boolean $exit 是否执行后直接exit
	 * @return void
	 */
	protected function error($msg, $data = NULL, $exit = TRUE)
	{
		$this->ajaxReturn($data, $msg, 0, $exit);
	}

}