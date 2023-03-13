<?php

namespace Shamrock\Instance\Html;

class HtmlFormEntity
{
    /**
     * @param $config
     * @return string|null
     * 获取html实体
     */
    public function get($config)
    {
        if (!is_array($config)){
            return null;
        }

        $html = '';
        foreach ($config as $htmlEntityName=>$htmlVal){

            // 预定值
            $label = isset($htmlVal['label'])?$htmlVal['label']:'';
            $default_value = isset($htmlVal['default_value'])?$htmlVal['default_value']:'';
            $placeholder = isset($htmlVal['placeholder'])?$htmlVal['placeholder']:'';

            $minLength = '';
            if (isset($htmlVal['minlength'])){
                $minLength = ' minlength="'.$htmlVal['minlength '].'" ';
            }

            $maxLength = '';
            if (isset($htmlVal['maxlength'])){
                $maxLength = ' maxlength="'.$htmlVal['maxlength  '].'" ';
            }

            $disabled = '';
            if (isset($htmlVal['disabled'])&&$htmlVal['disabled']==true){
                $disabled = ' disabled="disabled" ';
            }

            $readonly = '';
            if (isset($htmlVal['readonly'])&&$htmlVal['readonly']==true){
                $readonly = ' readonly="readonly" ';
            }

            $required = '';
            if (isset($htmlVal['required'])&&$htmlVal['required']==true){
                $required = ' required="required" ';
            }


            if (isset($htmlVal['element'])){
                switch ($htmlVal['element']){
                    case 'input@text':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                                    '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' '.$minLength.' '.$maxLength.' type="text" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                                    '</div>'.
                                '</div>';
                        break;
                    case 'input@password':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ''.$readonly.' '.$disabled.' '.$minLength.' '.$maxLength.' type="password" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@radio':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10">';
                        $element = '';
                        if(isset($htmlVal['vars'])){
                            foreach ($htmlVal['vars'] as $key=>$val ){
                                foreach ($val as $subKey=>$subVal){
                                    if ($subVal==$default_value){
                                        $element .= $subKey.'<input '.$readonly.' '.$disabled.' type="radio" name="'.$htmlEntityName.'" checked class="form-control" value="'.$subVal.'">';
                                    }else{
                                        $element .= $subKey.'<input '.$readonly.' '.$disabled.' type="radio" name="'.$htmlEntityName.'" class="form-control" value="'.$subVal.'">';
                                    }
                                }
                            }
                        }

                        $html .= $element;

                        $html .='<span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@checkbox':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10">';
                        $element = '';
                        if(isset($htmlVal['vars'])){
                            foreach ($htmlVal['vars'] as $key=>$val ){
                                foreach ($val as $subKey=>$subVal){
                                    if ($subVal==$default_value){
                                        $element .= $subKey.'<input '.$readonly.' '.$disabled.' type="checkbox" name="'.$htmlEntityName.'" checked="checked" class="form-control" value="'.$subVal.'">';
                                    }else{
                                        $element .= $subKey.'<input '.$readonly.' '.$disabled.' type="checkbox" name="'.$htmlEntityName.'" class="form-control" value="'.$subVal.'">';
                                    }
                                }
                            }
                        }

                        $html .= $element;

                        $html .='<span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@number':
                        $min = '';
                        if (isset($htmlVal['min'])){
                            $min = 'min="'.$htmlVal['min'].'"';
                        }

                        $max = '';
                        if (isset($htmlVal['max'])){
                            $max = 'max="'.$htmlVal['max'].'"';
                        }

                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' '.$minLength.' '.$maxLength.' '.$min.' '.$max.' type="number" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@date':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="date" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@color':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="color" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@range':
                        $min = '';
                        if (isset($htmlVal['min'])){
                            $min = 'min="'.$htmlVal['min'].'"';
                        }

                        $max = '';
                        if (isset($htmlVal['max'])){
                            $max = 'max="'.$htmlVal['max'].'"';
                        }
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' '.$min.' '.$max.' type="range" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@time':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="time" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@datetime':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="datetime" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@email':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="email" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@search':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="search" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@tel':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><input '.$required. ' '.$readonly.' '.$disabled.' type="tel" name="'.$htmlEntityName.'" class="form-control" value="'.$default_value.'"> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@select':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><select '.$readonly.' '.$disabled.' name="'.$htmlEntityName.'">';
                        $element = '';
                        if(isset($htmlVal['vars'])){
                            foreach ($htmlVal['vars'] as $key=>$val ){
                                foreach ($val as $subKey=>$subVal){
                                    if ($subVal==$default_value){
                                        $element .= '<option selected="selected" class="form-control" value="'.$subVal.'">'.$subKey.'</option>';
                                    }else{
                                        $element .=  '<option class="form-control" value="'.$subVal.'">'.$subKey.'</option>';
                                    }
                                }
                            }
                        }

                        $element .='</select>';
                        $html .= $element;

                        $html .='<span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    case 'input@textarea':
                        $html .='<div class="form-group"><label class="col-sm-2 control-label">'.$label.'</label>'.
                            '<div class="col-sm-10"><textarea '.$required. ' '.$readonly.' '.$disabled.' name="'.$htmlEntityName.'" class="form-control" >'.$default_value.'</textarea> <span class="help-block m-b-none">'.$placeholder.'</span>'.
                            '</div>'.
                            '</div>';
                        break;
                    default:
                        $html .='';
                        break;
                }
            }
        }

        return $html;
    }
}