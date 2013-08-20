<?php
/**
 * CModelEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CModelEvent class.
 *
 * CModelEvent represents the event parameters needed by events raised by a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
// 模型专用的事件类
class CModelEvent extends CEvent
{
	/**
	 * @var boolean whether the model is in valid status and should continue its normal method execution cycles. Defaults to true.
	 * For example, when this event is raised in a {@link CFormModel} object that is executing {@link CModel::beforeValidate},
	 * if this property is set false by the event handler, the {@link CModel::validate} method will quit after handling this event.
	 * If true, the normal execution cycles will continue, including performing the real validations and calling
	 * {@link CModel::afterValidate}.
	 */
	// 模型是否有效的标志，默认为true
	// 例如，当这个事件在模型执行beforeValidate时，如果这个属性被设置为false，那么模型执行验证将在执行为事件后退出。
	// 如果设置设置为true，将继续执行
	public $isValid=true;
}
