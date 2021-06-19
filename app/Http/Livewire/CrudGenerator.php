<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CrudGenerator extends Component
{
    public $table;
    public $filename;
    public $modelname;
    public $folder_namespace = 'Admin';
    public $form_type;

    public $tables = [];
    public $columns = [];
    public $field = [];
    public $field_column = [];
    public $field_columns = [];
    public $have_richtext = false;
    public function mount()
    {
        $columns = Schema::getAllTables();
        foreach ($columns as $key => $value) {
            $this->tables[] = [
                'name' => $value->Tables_in_absenskuy
            ];
        }
    }
    public function render()
    {
        if ($this->table) {
            if (count($this->columns) < 1) {
                $this->columns = Schema::getColumnListing($this->table);
                $fields = [];
                $labels = [];
                foreach ($this->columns as $key => $value) {
                    $fields[$value] = 'text';
                    $labels[$value] = str_replace('_', ' ', $value);
                }
                $this->field = [
                    'type' => $fields,
                    'label' => $labels,
                ];
            }

            $this->columns = $this->columns;
            $this->field = $this->field;
            $this->field_column = array_merge_recursive($this->field['type'], $this->field['label']);
        }
        return view('livewire.crud-generator');
    }

    protected function getStub($type)
    {
        return file_get_contents(resource_path("stubs/$type.stub"));
    }

    public function generate()
    {
        $field_columns = $this->_getFieldColumns();
        $controller_name = $this->filename;
        $view_name = str_replace('_', '-', $this->table);

        $controllerTemplate = $this->controllerTemplate();
        $viewTemplate = $this->viewTemplate($field_columns);
        $modelTemplate = $this->modelTemplate($field_columns);
        file_put_contents(app_path("/Http/Livewire/Admin/{$controller_name}.php"), $controllerTemplate);
        if ($this->form_type == 'modal') {
            file_put_contents(resource_path("/views/livewire/admin/{$view_name}.blade.php"), $viewTemplate);
        }

        if ($this->form_type == 'form') {
            file_put_contents(resource_path("/views/livewire/admin/{$view_name}.blade.php"), $viewTemplate);
        }

        // file_put_contents(app_path("/Models/{$this->filename}.php"), $modelTemplate);

        $this->_reset();
        return $this->emit('showAlert', ['msg' => 'CRUD Berhasil Dibuat']);
    }

    public function controllerTemplate()
    {
        $controllerTemplate = str_replace(
            [
                '[modelName]',
                '[folderNamespace]',
                '[fileName]',
                '[tableId]',
                '[tableColumn]',
                '[table_name]',
                '[viewName]',
                '[useForm]',
                '[useModal]',
                '[formRequest]',
                '[getTableId]',
                '[makeRules]',
                '[getDataById]',
                '[resetForm]',
            ],
            [
                $this->filename,
                $this->folder_namespace,
                $this->filename,
                'public $' . $this->table . '_id',
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getTableColumn())),
                $this->table,
                str_replace('_', '-', $this->table),
                $this->form_type == 'form' ? 'true' : 'false',
                $this->form_type == 'modal' ? 'true' : 'false',
                str_replace('<br>', '', implode(',' . PHP_EOL, $this->_getFormRequest())),
                '$this->' . $this->table . '_id',
                str_replace('<br>', '', implode(',' . PHP_EOL, $this->_makeRules())),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_getDataById($this->table))),
                str_replace('<br>', '', implode(';' . PHP_EOL, $this->_resetForm())),
            ],
            $this->getStub('Controller')
        );

        return $controllerTemplate;
    }

    public function viewTemplate($field_columns)
    {
        $viewTemplate = str_replace(
            [
                '[formInput]',
                '[label]',
                '[assetRichText]',
                '[richText]',
                '[itemLabel]',
                '[itemValue]',
            ],
            [
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_makeFormInput($field_columns))),
                str_replace('_', '-', $this->table),
                $this->have_richtext ? '<script src="{{asset(\'assets/js/plugin/summernote/summernote-bs4.min.js\')}}"></script>' : null,
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getRichText($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getItemLabel($field_columns))),
                str_replace('<br>', '', implode('' . PHP_EOL, $this->_getItemValue($field_columns))),
            ],
            $this->getStub($this->form_type == 'modal' ? 'ViewModal' : 'View')
        );

        return $viewTemplate;
    }

    public function modelTemplate($field_columns)
    {
        $modelTemplate = str_replace(
            [
                '[fileName]',
                '[fillable]',
                '[dates]',
            ],
            [
                $this->filename,
                implode(',', $this->_getFillable($field_columns)),
                implode(',', $this->_getDates($field_columns)),
            ],
            $this->getStub('Model')
        );

        return $modelTemplate;
    }

    public function _getFillable($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = "'$key'";
        }
        return $column_render;
    }

    public function _getDates($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if ($value['type'] == 'date') {
                $column_render[] = "'$key'";
            }
        }
        return $column_render;
    }

    public function _makeFormInput($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['richtext'])) {
                $this->have_richtext = true;
            }

            if (in_array($value['type'], ['textarea'])) {
                $column_render[] = '<x-textarea type="' . $value['type'] . '" name="' . $key . '" label="' . $value['label'] . '" />';
            }

            if (in_array($value['type'], ['richtext'])) {
                $column_render[] = '<div wire:ignore class="form-group @error(\'' . $key . '\')has-error has-feedback @enderror">
                                <label for="' . $key . '" class="text-capitalize">' . $value['label'] . '</label>
                                <textarea wire:model="' . $key . '" id="' . $key . '" class="form-control"></textarea>
                                @error(\'' . $key . '\')
                                <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>';
            }

            if (in_array($value['type'], ['text', 'number', 'hidden', 'date'])) {
                $column_render[] = '<x-text-field type="' . $value['type'] . '" name="' . $key . '" label="' . $value['label'] . '" />';
            }

            if ($value['type'] == 'select') {
                $column_render[] = '<x-select name="' . $key . '" label="' . $value['label'] . '" ><option value="">Select ' . $value['label'] . '</option></x-select>';
            }
        }

        return $column_render;
    }

    public function _getItemLabel($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = '<td>' . $value['label'] . '</td>';
        }
        return $column_render;
    }

    public function _getItemValue($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            $column_render[] = '<td>{{ $item->' . $key . ' }}</td>';
        }
        return $column_render;
    }

    public function _getRichText($field_columns)
    {
        $column_render = [];
        foreach ($field_columns as $key => $value) {
            if (in_array($value['type'], ['richtext'])) {
                $column_render[] = "$('#$key').summernote({
            placeholder: '$key',
            fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
            tabsize: 2,
            height: 300,
            callbacks: {
                        onChange: function(contents, \$editable) {
                            @this.set('$key', contents);
                        }
                    }
            });";
            }
        }
        return $column_render;
    }

    public function _getTableColumn()
    {
        $column_render = [];
        foreach ($this->columns as $column) {
            $column_render[] = 'public $' . $column;
        }

        return $column_render;
    }

    public function _getFormRequest()
    {
        $form_render = [];
        foreach ($this->columns as $column) {
            $form_render[] = '\'' . $column . '\'  => $this->' . $column . '';
        }

        return $form_render;
    }

    public function _makeRules()
    {
        $rules = [];
        foreach ($this->columns as $column) {
            $rules[] = '\'' . $column . '\'  => \'required\'';
        }

        return $rules;
    }

    public function _getDataById($table_name)
    {
        $rules = [];
        foreach ($this->columns as $column) {
            $rules[] = '$this->' . $column . ' = $' . $table_name . '->' . $column . '';
        }

        return $rules;
    }

    public function _resetForm()
    {
        $reset_form = [];
        foreach ($this->columns as $column) {
            $reset_form[] = '$this->' . $column . ' = null';
        }

        return $reset_form;
    }

    public function _getFieldColumns()
    {
        $field_column = [];
        foreach ($this->field_column as $key => $value) {
            $mergered = [];
            for ($i = 0; $i < count($value); $i++) {
                if ($i == 0) {
                    $mergered['type'] = $value[$i];
                }
                if ($i == 1) {
                    $mergered['label'] = $value[$i];
                }
            }

            $field_column[$key] = $mergered;
            $mergered = [];
        }
        return $field_column;
    }

    public function delete($key, $value)
    {
        unset($this->columns[$key]);
        unset($this->field['type'][$value]);
        unset($this->field['label'][$value]);
    }

    public function _reset()
    {
        $this->table = null;
        $this->filename = null;
        $this->modelname = null;
        $this->form_type = null;
        $this->folder_namespace = 'Admin';

        $this->tables = [];
        $this->columns = [];
        $this->field = [];
        $this->field_column = [];
        $this->field_columns = [];
        $this->have_richtext = false;
    }
}
