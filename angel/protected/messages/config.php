<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..', // 需要翻译的目录，也就从这个目录的文件里面寻找 Yii::t 标记
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages', // 翻译文件的保存目录
	'languages'=>array('zh_cn'), // 需要翻译成的语言
	'fileTypes'=>array('php'), // 需要翻译的文件类型，也就从哪些文件里面寻找 Yii::t 标记
	'overwrite'=>true, // 翻译的文件是否需要覆盖，如果存在的话
	'exclude'=>array(
		'.svn',
		'.gitignore',
		'yiilite.php',
		'yiit.php',
		'/i18n/data',
		'/messages',
		'/vendors',
		'/web/js',
	), // 需要忽略的目录
);
