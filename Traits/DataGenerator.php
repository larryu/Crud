<?php

namespace App\Containers\Crud\Traits;

use stdClass;
use Illuminate\Support\Collection;

/**
 * DataGenerator Trait.
 */
/**
 * TODO: this trait should be a abstrac class?
 */
trait DataGenerator
{
    /**
     * @var boolean
     */
    public $hasSoftDeleteColumn = false;

    /**
     * @var boolean
     */
    public $hasLaravelTimestamps = false;

    /**
     * @var boolean
     */
    public $hasCreatedAtColumn = false;

    /**
     * @var boolean
     */
    public $hasUpdatedAtColumn = false;

    /**
     * @var string
     */
    public $primaryKey = '';

    public function parseFields($request)
    {
        $fields = array();

        foreach ($request->get('field') as $field_data) {
            $field = new stdClass();
            $field->name = $field_data['name'];
            $field->label = $field_data['label'];
            $field->type = $field_data['type'];
            $field->required = isset($field_data['required']);
            $field->defValue = $field_data['defValue'];
            $field->key = $field_data['key'];
            $field->maxLength = $field_data['maxLength'];
            $field->namespace = $field_data['namespace'];
            $field->relation = $field_data['relation'];
            $field->fillable = isset($field_data['fillable']);
            $field->hidden = isset($field_data['hidden']);
            $field->on_index_table = isset($field_data['on_index_table']);
            $field->on_create_form = isset($field_data['on_create_form']);
            $field->on_update_form = isset($field_data['on_update_form']);
            $field->testData = empty($field_data['testData']) ? '""' : $field_data['testData'];
            if ($field->name == 'deleted_at' && empty($field_data['testData'])) {
                $field->testData = 'null';
            }
            $field->testDataUpdate = empty($field_data['testDataUpdate']) ? '""' : $field_data['testDataUpdate'];
            if ($field->name == 'deleted_at' && empty($field_data['testDataUpdate'])) {
                $field->testDataUpdate = 'null';
            }
            $field->validation_rules = $field_data['validation_rules'];

            $fields[$field->name] = $field;

            // some data checks
            $field_data['name'] == "created_at" ? ($this->hasCreatedAtColumn = true) : null;
            $field_data['name'] == "updated_at" ? ($this->hasUpdatedAtColumn = true) : null;
            $field_data['name'] == "deleted_at" ? ($this->hasSoftDeleteColumn = true) : null;
            $field_data['key'] == "PRI" ? ($this->primaryKey = $field_data['name']) : null;
        }

        $this->fields = $fields;

        // some final checks
        $this->hasCreatedAtColumn && $this->hasUpdatedAtColumn
            ? ($this->hasLaravelTimestamps = true)
            : null;

        return collect($fields);
    }

    public function getFormModelConfigArray(Collection $fields)
    {
        $config = [];

        foreach ($fields as $field) {
            $fieldConfig = [];
            $fieldConfig['name'] = $field->name;
            $fieldConfig['type'] = $this->getWidgetType($field);
            $fieldConfig['placeholder'] = '';
            $fieldConfig['value'] = '';
            $fieldConfig['min'] = '';
            $fieldConfig['max'] = '';
            // style classes
            $fieldConfig['mainWrapperClass'] = '';
            $fieldConfig['labelClass'] = '';
            $fieldConfig['controlWrapperClass'] = '';
            $fieldConfig['controlClass'] = '';

            $fieldConfig['visibility'] = [
                'create' => $field->on_create_form,
                'details' => !$field->hidden,
                'edit' => $field->on_update_form,
            ];

            if ($field->namespace) {
                $fieldConfig['dynamicOptions'] = [
                    'data' => str_plural(class_basename($field->namespace))
                ];
            }

            if ($field->type === 'tinyint') {
                $fieldConfig['option'] = [
                    'value' => 'true',
                    'label' => trans('crud::templates.yes'),
                ];
            }

            if ($field->type == "enum") {
                $fieldConfig['options'] = $this->getEnumValuesArray($field->name);
            }

            if ($field->validation_rules) {
                $fieldConfig['validation'] = explode('|', $field->validation_rules);
            }

            $config[$field->name] = $fieldConfig;
        }

        $config['_options_'] = ['model' => $this->slugEntityName()];

        return $config;
    }

    public function getWidgetType($field)
    {
        $type = "";

        switch ($field->type) {
            case 'enum':
                $type = "radiobutton";
                break;

            case 'bigint':
            case 'float':
            case 'double':
                $type = "number";
                break;
            
            case 'int': {
                $type = "number";
                if ($field->namespace) {
                    $type = "select";
                }
            }
                break;

            case 'tinyint':
                $type = "checkbox";
                break;

            case 'text':
                $type = "textarea";
                break;
            
            default:
                $type = "text";
                break;
        }

        return $type;
    }

    public function getFakeData($field, bool $onlyFaker = false)
    {
        // null para los campos de fecha de eliminación
        if ($field->name == 'deleted_at') {
            return 'null';
        }

        if ($field->type == 'timestamp' || $field->type == 'datetime') {
            return $onlyFaker ? '$faker->date(\'Y-m-d H:i:s\')' : '$date->toDateTimeString()';
        }

        if ($field->type == 'date') {
            return $onlyFaker ? '$faker->date(\'Y-m-d\')' : '$date->toDateString()';
        }

        if ($field->type == 'varchar') {
            return '$faker->sentence';
        }

        if ($field->type == 'text') {
            return '$faker->text';
        }

        if ($field->type == 'int' && $field->namespace) {
            $modelVariableName = $this->variableFromNamespace($field->namespace, $singular = false);

            return '$faker->randomElement('.$modelVariableName.')';
        }

        if (($field->type == 'int' || $field->type == 'bigint') && !$field->namespace) {
            return '$faker->randomNumber()';
        }

        if ($field->type == 'float' || $field->type == 'double') {
            return '$faker->randomFloat()';
        }

        if ($field->type == 'tinyint') {
            return '$faker->boolean(60)';
        }

        if ($field->type == 'enum') {
            $enumValues = $this->getMysqlTableColumnEnumValues($field->name);

            $enumValues = str_replace('enum(', '[', $enumValues);
            $enumValues = str_replace(')', ']', $enumValues);

            return "\$faker->randomElement($enumValues)";
        }

        // default
        return '$faker->';
    }

    /**
     * @return array
     */
    public function getEnumValuesArray(string $column)
    {
        $values = $this->getMysqlTableColumnEnumValues($column);
        $values = str_replace('enum(', '', $values);
        $values = str_replace('\')', '\'', $values);
        $values = str_replace('\'', '', $values);

        return explode(',', $values);
    }

    /**
     * TODO: where ti put this method? And what if the app is runing on sqlite?
     *
     * @param string $column The table column name
     *
     * @return string
     */
    public function getMysqlTableColumnEnumValues(string $column)
    {
        $prefix = config('database.connections.'.env('DB_CONNECTION').'.prefix');

        return \DB::select(
            \DB::raw(
                "SHOW COLUMNS FROM {$prefix}{$this->tableName} WHERE Field = '$column'"
            )
        )[0]->Type;
    }

    /**
     * Obtiene el tipo de dato nativo del campo, de la base de datos a PHP para
     * mapear casting de atributos de modelos.
     *
     * @param stdClass $field
     *
     * @return string
     */
    public function getFieldTypeCast($field)
    {
        $stringTypes = [
            'varchar',
            'char',
            'text',
            'enum',
            'time',
        ];
        $cast = '';

        if (in_array($field->type, $stringTypes)) {
            $cast = 'string';
        }

        if ($field->type == 'double') {
            $cast = 'double';
        }

        if ($field->type == 'float') {
            $cast = 'float';
        }

        if ($field->type == 'tinyint') {
            $cast = 'boolean';
        }

        if ($field->type == 'int' || $field->type == 'bigint') {
            $cast = 'int';
        }

        if ($field->type == 'json') {
            $cast = 'array';
        }

        if ($field->type == 'date') {
            $cast = 'date';
        }

        if ($field->type == 'datetime' || $field->type == 'timestamp') {
            $cast = 'datetime';
        }

        return $cast;
    }

    public function jsDataTypeFromField(stdClass $field)
    {
        if ($field->name == 'id') {
            return 'string';
        }
        
        $stringTypes = [
            'varchar',
            'char',
            'text',
            'enum',
            'time',
            'date'
        ];
        $intTypes = ['int', 'bigint'];
        $boolenTypes = ['tinyint', 'bool', 'boolean'];
        $dateTimeTypes = ['datetime', 'timestamp'];

        if ($field->type == "json") {
            return "{}";
        }

        if (in_array($field->type, $boolenTypes)) {
            return "boolean";
        }

        if (in_array($field->type, $stringTypes)) {
            return "string";
        }

        if (in_array($field->type, $intTypes)) {
            return "number";
        }

        if (in_array($field->type, $dateTimeTypes)) {
            return "Timestamps";
        }

        return "null";
    }

    /**
     * Configures the index file imports for components, effects, containers,
     * etc... If the file already exists, then append the convenient imports to
     * the file, if the file does not exist, then creates the file with the
     * respective imports.
     *
     * @param  string $indexFilePath The final index file location
     * @param  string $template      The index template file to create
     * @param  string $className     The class name to search for on existing index file
     * @param  string $fileName      The file name where the above class is located
     * @return void                
     */
    public function setupIndexFile(string $indexFilePath, string $template, string $className, string $fileName)
    {
        if (file_exists($indexFilePath)) {
            $indexFileContents = file_get_contents($indexFilePath);
            
            if (strpos($indexFileContents, $className)) {
                session()->push('warning', $className.' already added on index file');
            } else {
                $replace = $this->indexFileReplacements($className, $fileName);

                $content = str_replace(
                    $this->indexStrToreplace,
                    $replace,
                    $indexFileContents
                );

                file_put_contents($indexFilePath, $content) === false
                    ? session()->push('error', $className." index file setup error")
                    : session()->push('success', $className." index file setup success");
            }
            
            return;
        }

        $content = view($template, [
            'gen' => $this,
            'fields' => $this->parseFields($this->request)
        ]);

        file_put_contents($indexFilePath, $content) === false
            ? session()->push('error', "Error on index $className file setup")
            : session()->push('success', "Index $className setup success");
    }

    /**
     * Prepare the replacements for an existing index file, the result will be
     * appened to the target file.
     *
     * @param  string $className The class name to import
     * @param  string $fileName  The file name where the above class is located
     * @return string
     */
    public function indexFileReplacements(string $className, string $fileName)
    {
        $classImport = "import { $className } from '$fileName'";
        
        if (isset($this->indexClassTemplate)) {
            $className = str_replace(':class', $className, $this->indexClassTemplate);
        }

        $classUsage = $this->indexStrToreplace."\n  $className,";

        return $classImport."\n".$classUsage;
    }
}
