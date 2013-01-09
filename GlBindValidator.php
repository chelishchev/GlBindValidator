<?php
/**
 * Валидатор, позволяющий привязывать к указанному атрибуту ошибки валидации атрибутов bindWith
 *
 * То есть проверка указанного атрибута сводится к проверки связанных с ним в rules().
 * И получается нечто вроде наследования правил других атрибутов.
 * @author Ivan Chelishchev <chelishchev@gmail.com>
 */
class GlBindValidator extends CValidator
{
    /**
     * @var string the ActiveRecord class name that should be used to
     * look for the attribute value being validated. Defaults to null, meaning using
     * the class of the object currently being validated.
     * You may use path alias to reference a class name here.
     * @see attributeName
     */
    public $className;
    /**
     * @var string the ActiveRecord class attribute name that should be
     * used to look for the attribute value being validated. Defaults to null,
     * meaning using the name of the attribute being validated.
     * @see className
     */
    public $attributeName;
    /**
     * @author Ivan Chelishchev <chelishchev@gmail.com>
     * @var string Массив, строка. Имена атрибутов от валидации которых зависит данный атрибут.
     */
    public $bindWith;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        //TODO можно повесить исключение, если не найден атрибут
        $bindAttr = (array) $this->bindWith;
        $errors   = array();
        foreach($bindAttr as $attr)
        {
            //очищаем ошибки для целевого атрибута. Делаем это для того, что возможна validate() только одного
            //указанного атрибута, а это означает, что привязанные атрибуты могли быть уже изменены после предыдущей
            //валидации. Поэтому очищаем ошибки и проходим процедуру заново.
            $object->clearErrors($attr);
        }
        unset($attr);

        //проходимся по валидаторам для действующего scenario
        foreach($object->getValidators() as $validator)
        {
            /** @var CValidator $validator */
            //проверяем целевой атрибут
            $validator->validate($object, $bindAttr);
        }

        foreach($bindAttr as $attr)
        {
            //накапливаем ошибки, которые получились после валидации целевых атрибутов
            $errors = array_merge($errors, $object->getErrors($attr));
        }
        unset($attr);

//        $object->clearErrors($attribute);
        if($errors)
        {
            foreach($errors as $error)
            {
                $this->addError($object, $attribute, $error);
            }
            unset($error);
        }
        unset($errors, $attribute);
    }
}
