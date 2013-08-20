<?php
/**
 * CPhpMessageSource class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPhpMessageSource represents a message source that stores translated messages in PHP scripts.
 *
 * CPhpMessageSource uses PHP files and arrays to keep message translations.
 * <ul>
 * <li>All translations are saved under the {@link basePath} directory.</li>
 * <li>Translations in one language are kept as PHP files under an individual subdirectory
 *   whose name is the same as the language ID. Each PHP file contains messages
 *   belonging to the same category, and the file name is the same as the category name.</li>
 * <li>Within a PHP file, an array of (source, translation) pairs is returned.
 * For example:
 * <pre>
 * return array(
 *     'original message 1' => 'translated message 1',
 *     'original message 2' => 'translated message 2',
 * );
 * </pre>
 * </li>
 * </ul>
 * When {@link cachingDuration} is set as a positive number, message translations will be cached.
 *
 * Messages for an extension class (e.g. a widget, a module) can be specially managed and used.
 * In particular, if a message belongs to an extension whose class name is Xyz, then the message category
 * can be specified in the format of 'Xyz.categoryName'. And the corresponding message file
 * is assumed to be 'BasePath/messages/LanguageID/categoryName.php', where 'BasePath' refers to
 * the directory that contains the extension class file. When using Yii::t() to translate an extension message,
 * the category name should be set as 'Xyz.categoryName'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.i18n
 * @since 1.0
 */

// 代表翻译的信息和信息源存储在PHP脚本
// CPhpMessageSource使用PHP文件和数组来保存翻译信息。
// 所有翻译被保存在basePath目录下。
// 每种语言都会保存在独立目录的PHP文件里面， 它的名字跟语言ID相同。 每个PHP文件都包含相同的信息类别，文件名称和类别名称相同。
// return array(
//     'original message 1' => 'translated message 1',
//     'original message 2' => 'translated message 2',
// );
// 当cachingDuration设置为正数，翻译的信息将被缓存
// 扩展类的信息（例如：挂件，模块）可以指定管理和使用。 特别注意： 如果信息名是Xyz的扩展，那么信息分类可以指定为'Xyz.categoryName'。 相关信息文件会被认为是‘BasePath/messages/LanguageID/categoryName.php’，在‘BasePath’关联到包含扩展类文件的目录。 当使用Yii::t()来翻译一个扩展信息时， 那么分类名称会被设为‘Xyz.categoryName’。
class CPhpMessageSource extends CMessageSource
{
	const CACHE_KEY_PREFIX='Yii.CPhpMessageSource.'; // 缓存的键值

	/**
	 * @var integer the time in seconds that the messages can remain valid in cache.
	 * Defaults to 0, meaning the caching is disabled.
	 */
	public $cachingDuration=0; // 表示在多少秒内 消息数据被缓存 0表示不缓存
	/**
	 * @var string the ID of the cache application component that is used to cache the messages.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching the messages.
	 */
	public $cacheID='cache'; // 缓存组件ID值
	/**
	 * @var string the base path for all translated messages. Defaults to null, meaning
	 * the "messages" subdirectory of the application directory (e.g. "protected/messages").
	 */
	public $basePath; // 翻译信息数据的目录 默认 protected/messages
	/**
	 * @var array the message paths for extensions that do not have a base class to use as category prefix.
	 * The format of the array should be:
	 * <pre>
	 * array(
	 *     'ExtensionName' => 'ext.ExtensionName.messages',
	 * )
	 * </pre>
	 * Where the key is the name of the extension and the value is the alias to the path
	 * of the "messages" subdirectory of the extension.
	 * When using Yii::t() to translate an extension message, the category name should be
	 * set as 'ExtensionName.categoryName'.
	 * Defaults to an empty array, meaning no extensions registered.
	 * @since 1.1.13
	 */
	
	// 扩展翻译信息数据的目录
	public $extensionPaths=array();

	private $_files=array(); // 保存翻译数据文件

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by preprocessing
	 * the user request data.
	 */
	public function init()
	{
		parent::init();

		// 指定翻译信息数据目录
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.messages');
	}

	/**
	 * Determines the message file name based on the given category and language.
	 * If the category name contains a dot, it will be split into the module class name and the category name.
	 * In this case, the message file will be assumed to be located within the 'messages' subdirectory of
	 * the directory containing the module class file.
	 * Otherwise, the message file is assumed to be under the {@link basePath}.
	 * @param string $category category name
	 * @param string $language language ID
	 * @return string the message file path
	 */
	
	// 根据分类和语言返回翻译数据文件
	protected function getMessageFile($category,$language)
	{
		// 如果不存在
		if(!isset($this->_files[$category][$language]))
		{
			// 如果分类中含有 . 符号 表示这是个扩展类使用的消息翻译
			if(($pos=strpos($category,'.'))!==false)
			{
				$extensionClass=substr($category,0,$pos);
				$extensionCategory=substr($category,$pos+1);
				// First check if there's an extension registered for this class.
				if(isset($this->extensionPaths[$extensionClass]))
					$this->_files[$category][$language]=Yii::getPathOfAlias($this->extensionPaths[$extensionClass]).DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				else
				{
					// No extension registered, need to find it.
					$class=new ReflectionClass($extensionClass);
					$this->_files[$category][$language]=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$extensionCategory.'.php';
				}
			}
			else
				$this->_files[$category][$language]=$this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
		}
		return $this->_files[$category][$language];
	}

	/**
	 * Loads the message translation for the specified language and category.
	 * @param string $category the message category
	 * @param string $language the target language
	 * @return array the loaded messages
	 */
	
	// 加载指定分类、语言的翻译数据
	protected function loadMessages($category,$language)
	{
		// 翻译数据的文件地址
		$messageFile=$this->getMessageFile($category,$language);

		// 如果设置了缓存
		if($this->cachingDuration>0 && $this->cacheID!==false && ($cache=Yii::app()->getComponent($this->cacheID))!==null)
		{
			$key=self::CACHE_KEY_PREFIX . $messageFile;
			if(($data=$cache->get($key))!==false)
				return unserialize($data);
		}

		if(is_file($messageFile))
		{
			$messages=include($messageFile);
			if(!is_array($messages))
				$messages=array();

			// 缓存翻译数据
			if(isset($cache))
			{
				$dependency=new CFileCacheDependency($messageFile);
				$cache->set($key,serialize($messages),$this->cachingDuration,$dependency);
			}
			return $messages;
		}
		else
			return array();
	}
}