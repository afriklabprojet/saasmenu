<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportExportMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'import_export_mappings';

    protected $fillable = [
        'name',
        'description',
        'data_type',
        'source_fields',
        'target_fields',
        'field_mappings',
        'transformations',
        'validation_rules',
        'is_default',
        'is_active',
        'user_id',
        'usage_count',
        'metadata',
    ];

    protected $casts = [
        'source_fields' => 'array',
        'target_fields' => 'array',
        'field_mappings' => 'array',
        'transformations' => 'array',
        'validation_rules' => 'array',
        'metadata' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'source_fields' => '[]',
        'target_fields' => '[]',
        'field_mappings' => '[]',
        'transformations' => '[]',
        'validation_rules' => '[]',
        'metadata' => '[]',
        'is_default' => false,
        'is_active' => true,
        'usage_count' => 0,
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mappings actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Mappings par défaut
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Mappings par type de données
     */
    public function scopeByDataType($query, $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Incrémenter l'usage
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Appliquer le mapping à des données
     */
    public function applyToData($data)
    {
        $mappedData = [];

        foreach ($data as $row) {
            $mappedRow = [];

            foreach ($this->field_mappings as $sourceField => $targetField) {
                if (isset($row[$sourceField])) {
                    $value = $row[$sourceField];

                    // Appliquer les transformations
                    $value = $this->applyTransformations($sourceField, $value);

                    $mappedRow[$targetField] = $value;
                }
            }

            $mappedData[] = $mappedRow;
        }

        return $mappedData;
    }

    /**
     * Appliquer les transformations à une valeur
     */
    protected function applyTransformations($field, $value)
    {
        if (!isset($this->transformations[$field])) {
            return $value;
        }

        $transformations = $this->transformations[$field];

        foreach ($transformations as $transformation) {
            $value = $this->applyTransformation($value, $transformation);
        }

        return $value;
    }

    /**
     * Appliquer une transformation spécifique
     */
    protected function applyTransformation($value, $transformation)
    {
        $type = $transformation['type'] ?? null;
        $params = $transformation['params'] ?? [];

        switch ($type) {
            case 'trim':
                return trim($value);

            case 'lowercase':
                return strtolower($value);

            case 'uppercase':
                return strtoupper($value);

            case 'capitalize':
                return ucwords(strtolower($value));

            case 'replace':
                return str_replace($params['search'] ?? '', $params['replace'] ?? '', $value);

            case 'regex_replace':
                return preg_replace($params['pattern'] ?? '', $params['replacement'] ?? '', $value);

            case 'substring':
                $start = $params['start'] ?? 0;
                $length = $params['length'] ?? null;
                return $length ? substr($value, $start, $length) : substr($value, $start);

            case 'date_format':
                $inputFormat = $params['input_format'] ?? 'Y-m-d';
                $outputFormat = $params['output_format'] ?? 'Y-m-d H:i:s';
                try {
                    $date = \DateTime::createFromFormat($inputFormat, $value);
                    return $date ? $date->format($outputFormat) : $value;
                } catch (\Exception $e) {
                    return $value;
                }

            case 'number_format':
                $decimals = $params['decimals'] ?? 2;
                $decimalPoint = $params['decimal_point'] ?? '.';
                $thousandsSep = $params['thousands_separator'] ?? ',';
                return number_format((float)$value, $decimals, $decimalPoint, $thousandsSep);

            case 'default_if_empty':
                return empty($value) ? ($params['default'] ?? '') : $value;

            case 'prefix':
                return ($params['prefix'] ?? '') . $value;

            case 'suffix':
                return $value . ($params['suffix'] ?? '');

            default:
                return $value;
        }
    }

    /**
     * Valider le mapping
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->field_mappings)) {
            $errors[] = 'Aucun mapping de champs défini';
        }

        foreach ($this->field_mappings as $source => $target) {
            if (empty($source) || empty($target)) {
                $errors[] = 'Mapping invalide: champ source ou cible vide';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Cloner le mapping
     */
    public function duplicate($newName = null)
    {
        $clone = $this->replicate();
        $clone->name = $newName ?? $this->name . ' (Copie)';
        $clone->is_default = false;
        $clone->usage_count = 0;
        $clone->save();

        return $clone;
    }
}

class ImportExportTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'import_export_templates';

    protected $fillable = [
        'name',
        'description',
        'type',
        'data_type',
        'format',
        'fields',
        'sample_data',
        'validation_rules',
        'transformations',
        'is_system',
        'is_active',
        'download_count',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'fields' => 'array',
        'sample_data' => 'array',
        'validation_rules' => 'array',
        'transformations' => 'array',
        'metadata' => 'array',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'download_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'fields' => '[]',
        'sample_data' => '[]',
        'validation_rules' => '[]',
        'transformations' => '[]',
        'metadata' => '[]',
        'is_system' => false,
        'is_active' => true,
        'download_count' => 0,
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Templates actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Templates système
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    /**
     * Templates par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Templates par type de données
     */
    public function scopeByDataType($query, $dataType)
    {
        return $query->where('data_type', $dataType);
    }

    /**
     * Templates par format
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Incrémenter le compteur de téléchargement
     */
    public function incrementDownload()
    {
        $this->increment('download_count');
    }

    /**
     * Générer le fichier template
     */
    public function generateFile()
    {
        // Cette méthode serait implémentée pour générer le fichier template
        // selon le format spécifié (CSV, Excel, etc.)

        // Placeholder pour l'implémentation
        return [
            'success' => true,
            'file_path' => 'templates/' . $this->name . '.' . $this->format,
            'filename' => $this->name . '.' . $this->format
        ];
    }

    /**
     * Obtenir les champs requis
     */
    public function getRequiredFields()
    {
        return array_filter($this->fields, function($field) {
            return ($field['required'] ?? false) === true;
        });
    }

    /**
     * Obtenir les champs optionnels
     */
    public function getOptionalFields()
    {
        return array_filter($this->fields, function($field) {
            return ($field['required'] ?? false) !== true;
        });
    }

    /**
     * Valider les données contre le template
     */
    public function validateData($data)
    {
        $errors = [];
        $warnings = [];

        foreach ($data as $index => $row) {
            $rowErrors = $this->validateRow($row, $index + 1);
            $errors = array_merge($errors, $rowErrors);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Valider une ligne de données
     */
    protected function validateRow($row, $lineNumber)
    {
        $errors = [];

        foreach ($this->fields as $fieldName => $fieldConfig) {
            $value = $row[$fieldName] ?? null;
            $rules = $fieldConfig['validation'] ?? [];

            foreach ($rules as $rule) {
                if (!$this->validateFieldRule($value, $rule)) {
                    $errors[] = "Ligne {$lineNumber}, champ '{$fieldName}': {$this->getValidationMessage($rule)}";
                }
            }
        }

        return $errors;
    }

    /**
     * Valider une règle de champ
     */
    protected function validateFieldRule($value, $rule)
    {
        if (is_string($rule)) {
            return $this->validateSimpleRule($value, $rule);
        }

        if (is_array($rule)) {
            $ruleName = $rule['rule'] ?? '';
            $ruleParams = $rule['params'] ?? [];
            return $this->validateComplexRule($value, $ruleName, $ruleParams);
        }

        return true;
    }

    /**
     * Valider une règle simple
     */
    protected function validateSimpleRule($value, $rule)
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'string':
                return is_string($value);
            case 'numeric':
                return is_numeric($value);
            case 'integer':
                return filter_var($value, FILTER_VALIDATE_INT) !== false;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;
            case 'boolean':
                return in_array(strtolower($value), ['true', 'false', '1', '0', 'yes', 'no']);
            case 'date':
                return strtotime($value) !== false;
            default:
                return true;
        }
    }

    /**
     * Valider une règle complexe
     */
    protected function validateComplexRule($value, $rule, $params)
    {
        switch ($rule) {
            case 'min':
                return is_numeric($value) ? $value >= $params['value'] : strlen($value) >= $params['value'];
            case 'max':
                return is_numeric($value) ? $value <= $params['value'] : strlen($value) <= $params['value'];
            case 'in':
                return in_array($value, $params['values']);
            case 'regex':
                return preg_match($params['pattern'], $value);
            case 'length':
                return strlen($value) === $params['value'];
            case 'between':
                $val = is_numeric($value) ? $value : strlen($value);
                return $val >= $params['min'] && $val <= $params['max'];
            default:
                return true;
        }
    }

    /**
     * Obtenir le message d'erreur de validation
     */
    protected function getValidationMessage($rule)
    {
        if (is_string($rule)) {
            return match($rule) {
                'required' => 'Ce champ est obligatoire',
                'string' => 'Ce champ doit être une chaîne de caractères',
                'numeric' => 'Ce champ doit être numérique',
                'integer' => 'Ce champ doit être un entier',
                'email' => 'Ce champ doit être une adresse email valide',
                'url' => 'Ce champ doit être une URL valide',
                'boolean' => 'Ce champ doit être un booléen (true/false, 1/0, yes/no)',
                'date' => 'Ce champ doit être une date valide',
                default => 'Valeur invalide'
            };
        }

        if (is_array($rule)) {
            $ruleName = $rule['rule'] ?? '';
            $params = $rule['params'] ?? [];

            return match($ruleName) {
                'min' => "La valeur doit être supérieure ou égale à {$params['value']}",
                'max' => "La valeur doit être inférieure ou égale à {$params['value']}",
                'in' => "La valeur doit être l'une des suivantes: " . implode(', ', $params['values']),
                'regex' => "La valeur ne correspond pas au format requis",
                'length' => "La longueur doit être exactement de {$params['value']} caractères",
                'between' => "La valeur doit être entre {$params['min']} et {$params['max']}",
                default => 'Valeur invalide'
            };
        }

        return 'Valeur invalide';
    }
}
