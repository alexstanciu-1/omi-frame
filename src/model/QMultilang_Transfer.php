<?php

class QMultilang_Transfer
{
    protected static $Allowed_Admin_Types = ['H2W_Superadmin', 'H2W_Admin'];

    public static function RequireBackendUser()
    {
        $user = \Omi\User::GetCurrentUser();

        if (!$user || !$user->Id)
            throw new \Exception('Authentication required.');

        $user_type = trim((string)($user->Type ?? ''));
        if (!in_array($user_type, static::$Allowed_Admin_Types, true))
            throw new \Exception('You do not have permission to import or export multilanguage data.');

        return $user;
    }

    public static function GetDefaultLanguage(): string
    {
        if (function_exists('tfh_get_default_language'))
            return (string)tfh_get_default_language();

        $default_language = defined('Q_DEFAULT_USER_LANGUAGE') ? strtolower(trim((string)Q_DEFAULT_USER_LANGUAGE)) : 'ro';
        return $default_language !== '' ? $default_language : 'ro';
    }

    public static function GetLanguages(): array
    {
        if (function_exists('tfh_get_languages'))
            $languages = array_values((array)tfh_get_languages());
        else
            $languages = [static::GetDefaultLanguage()];

        $default_language = static::GetDefaultLanguage();
        $normalized = [];

        foreach ($languages as $language_code)
        {
            $language_code = strtolower(trim((string)$language_code));
            if ($language_code === '')
                continue;

            $normalized[$language_code] = $language_code;
        }

        unset($normalized[$default_language]);

        return array_merge([$default_language], array_values($normalized));
    }

    public static function DecodeAssoc($value)
    {
        if (function_exists('tfh_multilang_decode_assoc'))
            return tfh_multilang_decode_assoc($value);

        if (is_array($value))
            return $value;

        if (!is_string($value))
            return null;

        $trimmed = trim($value);
        if ($trimmed === '')
            return [];

        if (($trimmed[0] !== '{') || (substr($trimmed, -1) !== '}'))
            return null;

        $decoded = json_decode($trimmed, true);
        if ((!is_array($decoded)) || (json_last_error() !== JSON_ERROR_NONE))
            return null;

        return $decoded;
    }

    public static function NormalizeTranslationMap($value): array
    {
        $decoded = static::DecodeAssoc($value);
        if ($decoded === null)
        {
            $legacy = is_scalar($value) ? trim((string)$value) : '';
            return ($legacy !== '') ? [static::GetDefaultLanguage() => $legacy] : [];
        }

        $normalized = [];
        foreach ((array)$decoded as $language_code => $translation)
        {
            if (!is_string($language_code))
                continue;

            $language_code = strtolower(trim($language_code));
            if ($language_code === '')
                continue;

            $normalized[$language_code] = is_scalar($translation) ? (string)$translation : '';
        }

        return $normalized;
    }

    public static function EncodeTranslationMap(array $translations): string
    {
        $normalized = [];
        foreach ($translations as $language_code => $translation)
        {
            if (!is_string($language_code))
                continue;

            $language_code = strtolower(trim($language_code));
            if ($language_code === '')
                continue;

            $normalized[$language_code] = is_scalar($translation) ? (string)$translation : '';
        }

        ksort($normalized);

        return json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public static function GetRegistry(): array
    {
        $languages = static::GetLanguages();
        $default_language = static::GetDefaultLanguage();
        $table_type_list = \QSqlModelInfoType::GetTableTypeList() ?: [];
        $classes = array_keys((array)$table_type_list);

        if (!$classes)
        {
            $classes = \QAutoload::GetAllWatchedClasses() ?: [];
            sort($classes, SORT_STRING);
        }

        $registry = [];

        foreach ($classes as $class_name)
        {
            if (($class_name === 'QModel') || !class_exists($class_name) || !is_subclass_of($class_name, 'QModel'))
                continue;

            $reflection = new \ReflectionClass($class_name);
            if ($reflection->isAbstract())
                continue;

            $type = \QModel::GetTypeByName($class_name);
            if (!$type || empty($type->properties) || empty($type->storage) || empty($type->storage['table']))
                continue;

            $properties = [];
            foreach ($type->properties as $property_name => $property)
            {
                if (!$property || empty($property->storage) || empty($property->storage['multilanguage']))
                    continue;

                if ($property->hasCollectionType())
                    continue;

                $properties[$property_name] = [
                    'class' => $class_name,
                    'property' => $property_name,
                    'languages' => $languages,
                ];
            }

            if ($properties)
                $registry[$class_name] = $properties;
        }

        ksort($registry, SORT_STRING);

        return $registry;
    }

    public static function ExportCsv(array $class_filter = null): string
    {
        static::RequireBackendUser();

        $registry = static::GetRegistry();
        if ($class_filter)
        {
            $class_filter = array_fill_keys(array_values($class_filter), true);
            $registry = array_filter($registry, function ($class_name) use ($class_filter) {
                return isset($class_filter[$class_name]);
            }, ARRAY_FILTER_USE_KEY);
        }

        $languages = static::GetLanguages();
        $file_pointer = fopen('php://temp', 'w+');

        $header = array_merge(['Class', 'ID', 'Property_Name'], $languages);
        fputcsv($file_pointer, $header);

        foreach ($registry as $class_name => $properties)
        {
            $selector_parts = ['Id'];
            foreach ($properties as $property_name => $_property_meta)
                $selector_parts[] = $property_name;

            $query = implode(', ', array_unique($selector_parts));
            $data_block = null;
            $records = $class_name::QueryAll($query, null, $data_block, true);

            foreach (($records ?: []) as $record)
            {
                $record_id = (int)($record->Id ?? 0);
                if (!$record_id)
                    continue;

                foreach ($properties as $property_name => $_property_meta)
                {
                    $translations = static::NormalizeTranslationMap($record->$property_name ?? null);
                    $line = [$class_name, $record_id, $property_name];

                    foreach ($languages as $language_code)
                        $line[] = $translations[$language_code] ?? '';

                    fputcsv($file_pointer, $line);
                }
            }
        }

        fseek($file_pointer, 0);
        $csv = stream_get_contents($file_pointer);
        fclose($file_pointer);

        return (string)$csv;
    }

    public static function ImportCsv(string $csv_content): array
    {
        static::RequireBackendUser();

        $registry = static::GetRegistry();
        $languages = static::GetLanguages();
        $required_headers = ['Class', 'ID', 'Property_Name'];
        $report = [
            'languages' => $languages,
            'rows_processed' => 0,
            'rows_updated' => 0,
            'rows_skipped' => 0,
            'errors' => [],
            'ignored_columns' => [],
        ];

        $csv_content = trim($csv_content);
        if ($csv_content === '')
            return $report;

        $file_pointer = fopen('php://temp', 'w+');
        fwrite($file_pointer, $csv_content);
        fseek($file_pointer, 0);

        $header = fgetcsv($file_pointer);
        if (!$header)
            throw new \Exception('Missing CSV header.');

        foreach ($header as $index => $column_name)
        {
            $column_name = trim((string)$column_name);
            if ($index === 0)
                $column_name = preg_replace('/^\xEF\xBB\xBF/', '', $column_name);
            $header[$index] = $column_name;
        }

        foreach ($required_headers as $required_header)
        {
            if (!in_array($required_header, $header, true))
                throw new \Exception('Missing required CSV column: ' . $required_header);
        }

        $language_columns = [];
        foreach ($header as $column_name)
        {
            if (in_array($column_name, $required_headers, true))
                continue;

            if (in_array($column_name, $languages, true))
                $language_columns[] = $column_name;
            else
                $report['ignored_columns'][] = $column_name;
        }

        $row_number = 1;
        while (($row = fgetcsv($file_pointer)) !== false)
        {
            $row_number++;
            if ($row === [null] || $row === false)
                continue;

            $row_data = [];
            foreach ($header as $index => $column_name)
                $row_data[$column_name] = isset($row[$index]) ? trim((string)$row[$index]) : '';

            $has_visible_data = false;
            foreach ($row_data as $value)
            {
                if ($value !== '')
                {
                    $has_visible_data = true;
                    break;
                }
            }
            if (!$has_visible_data)
                continue;

            $report['rows_processed']++;

            $class_name = $row_data['Class'] ?? '';
            $property_name = $row_data['Property_Name'] ?? '';
            $record_id = (int)($row_data['ID'] ?? 0);

            if ((!$class_name) || (!isset($registry[$class_name])))
            {
                $report['errors'][] = ['row' => $row_number, 'message' => 'Invalid class.'];
                $report['rows_skipped']++;
                continue;
            }

            if ((!$property_name) || (!isset($registry[$class_name][$property_name])))
            {
                $report['errors'][] = ['row' => $row_number, 'message' => 'Invalid multilanguage property.'];
                $report['rows_skipped']++;
                continue;
            }

            if ($record_id <= 0)
            {
                $report['errors'][] = ['row' => $row_number, 'message' => 'Invalid record ID.'];
                $report['rows_skipped']++;
                continue;
            }

            $data_block = null;
            $record = $class_name::QueryById($record_id, 'Id, ' . $property_name, $data_block, true);
            if (!$record || !($record->Id ?? null))
            {
                $report['errors'][] = ['row' => $row_number, 'message' => 'Record not found.'];
                $report['rows_skipped']++;
                continue;
            }

            $translations = static::NormalizeTranslationMap($record->$property_name ?? null);
            $changed = false;

            foreach ($language_columns as $language_code)
            {
                $incoming_value = $row_data[$language_code] ?? '';
                if ($incoming_value === '')
                    continue;

                if (!array_key_exists($language_code, $translations) || ((string)$translations[$language_code] !== $incoming_value))
                {
                    $translations[$language_code] = $incoming_value;
                    $changed = true;
                }
            }

            if (!$changed)
            {
                $report['rows_skipped']++;
                continue;
            }

            $record->$property_name = static::EncodeTranslationMap($translations);
            $record->update('Id, ' . $property_name);

            $report['rows_updated']++;
        }

        fclose($file_pointer);

        $report['ignored_columns'] = array_values(array_unique(array_filter($report['ignored_columns'])));

        return $report;
    }
}
