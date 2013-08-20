<?php
/**
 * CTheme class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTheme represents an application theme.
 *
 * @property string $name Theme name.
 * @property string $baseUrl The relative URL to the theme folder (without ending slash).
 * @property string $basePath The file path to the theme folder.
 * @property string $viewPath The path for controller views. Defaults to 'ThemeRoot/views'.
 * @property string $systemViewPath The path for system views. Defaults to 'ThemeRoot/views/system'.
 * @property string $skinPath The path for widget skins. Defaults to 'ThemeRoot/views/skins'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */

// 主题类
// 代表一个应用的主题
class CTheme extends CComponent
{
	private $_name;
	private $_basePath;
	private $_baseUrl;

	/**
	 * Constructor.
	 * @param string $name name of the theme
	 * @param string $basePath base theme path
	 * @param string $baseUrl base theme URL
	 */
	// $name 主题的名称
	// $basePath 主题的路径
	public function __construct($name,$basePath,$baseUrl)
	{
		$this->_name=$name;
		$this->_baseUrl=$baseUrl;
		$this->_basePath=$basePath;
	}

	/**
	 * @return string theme name
	 */
	// 返回主题名称
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the relative URL to the theme folder (without ending slash)
	 */
	// 返回主题的url（不带最后的 / ）
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * @return string the file path to the theme folder
	 */
	// 返回主题目录
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * @return string the path for controller views. Defaults to 'ThemeRoot/views'.
	 */
	// 返回主题视图目录
	public function getViewPath()
	{
		return $this->_basePath.DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @return string the path for system views. Defaults to 'ThemeRoot/views/system'.
	 */
	// 返回主题系统视图目录
	public function getSystemViewPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * @return string the path for widget skins. Defaults to 'ThemeRoot/views/skins'.
	 * @since 1.1
	 */
	// 返回主题小工具皮肤目录
	public function getSkinPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'skins';
	}

	/**
	 * Finds the view file for the specified controller's view.
	 * @param CController $controller the controller
	 * @param string $viewName the view name
	 * @return string the view file path. False if the file does not exist.
	 */
	// 返回视图文件
	public function getViewFile($controller,$viewName)
	{
		$moduleViewPath=$this->getViewPath();

		// 如果是某个模块下的控制器
		if(($module=$controller->getModule())!==null)
			$moduleViewPath.='/'.$module->getId();

		return $controller->resolveViewFile($viewName,$this->getViewPath().'/'.$controller->getUniqueId(),$this->getViewPath(),$moduleViewPath);
	}

	/**
	 * Finds the layout file for the specified controller's layout.
	 * @param CController $controller the controller
	 * @param string $layoutName the layout name
	 * @return string the layout file path. False if the file does not exist.
	 */
	// 返回布局文件
	public function getLayoutFile($controller,$layoutName)
	{
		$moduleViewPath=$basePath=$this->getViewPath();
		$module=$controller->getModule();

		if(empty($layoutName))
		{
			// 循环的查找父模块
			while($module!==null)
			{
				if($module->layout===false) // 我们如何不想使用布局，设置个false，就可以，其他如 null 不行滴
					return false;
				if(!empty($module->layout))
					break;
				$module=$module->getParentModule();
			}

			// 找不到属于哪个模块，使用app的layout属性替代
			if($module===null)
				$layoutName=Yii::app()->layout;
			// 如果找到了
			else
			{
				$layoutName=$module->layout;
				$moduleViewPath.='/'.$module->getId();
			}
		}
		elseif($module!==null)
			$moduleViewPath.='/'.$module->getId();

		return $controller->resolveViewFile($layoutName,$moduleViewPath.'/layouts',$basePath,$moduleViewPath);
	}
}
