<?php

namespace Core\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    protected $additionalFields = [];
    protected static $collectionAdditionalFields = [];

    public function __construct($resource, array|int $additionalFields = [])
    {
        parent::__construct($resource);
        // Если передан индекс (число), используем статические поля для коллекции
        if (is_int($additionalFields) || empty($additionalFields)) {
            $this->additionalFields = static::$collectionAdditionalFields;
        } else {
            $this->additionalFields = $additionalFields;
        }
    }

    public static function collection($resource, array $additionalFields = [])
    {
        static::$collectionAdditionalFields = $additionalFields;
        return parent::collection($resource);
    }

    protected function getAdditionalData()
    {
        return [];
    }

    public function toArray($request)
    {
        $data = [];
        $additionalData = $this->getAdditionalData();

        foreach ($this->additionalFields as $field) {
            if (!array_key_exists($field, $additionalData)) {
                continue;
            }

            // ⏳ Выполняем только сейчас
            $value = $additionalData[$field];
            if ($value instanceof \Closure) {
                $value = $value();
            }

            // 🔗 Если это массив — мержим
            if (is_array($value)) {
                // Если значение является массивом, используем его ключи и значения
                $data = array_merge($data, $value);
            } else {
                // Если значение не массив, добавляем как есть
                $data[$field] = $value;
            }
        }

        return $data;
    }
}
