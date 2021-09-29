<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\ABPTable;


class ABPUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // здесь проверяем наличие прав на изменения
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];
        // $table_name = $this->route('table');
        // $ABP = new ABPTable();
        // $table = $ABP->new_table($table_name);
        // $connection = $table->connection();
        // $model = $table->model();
        // foreach ($model as $field) {
        //     $rule = [];
        //     $has_rules = false;
        //     if (isset($field["require"]) && $field["require"]==true) {
        //         $rule[] = 'required';
        //         $has_rules = true;
        //     }
        //     if (isset($field["max"])) {
        //         $rule[] = "max:".$field["max"];
        //         $has_rules = true;
        //     }
        //     if (isset($field["index"]) && $field["index"]=="unique") {
        //         $rule[] = Rule::unique($connection.'.'.$table_name,$field["name"]);
        //         $has_rules = true;
        //     }
        //     if ($has_rules) $rules[$field["name"]] = $rule;
        // }

        return $rules;
    }
}
