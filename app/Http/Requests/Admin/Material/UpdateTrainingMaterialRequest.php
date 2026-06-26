<?php

namespace App\Http\Requests\Admin\Material;

use App\Models\TrainingMaterial;

class UpdateTrainingMaterialRequest extends StoreTrainingMaterialRequest
{
    protected function requiresFileUpload(): bool
    {
        /** @var TrainingMaterial|null $material */
        $material = $this->route('training_material');

        if ($this->input('material_type') !== 'file') {
            return false;
        }

        return $material === null || $material->material_type !== 'file' || empty($material->file_path);
    }

    protected function requiresUrl(): bool
    {
        /** @var TrainingMaterial|null $material */
        $material = $this->route('training_material');

        if ($this->input('material_type') !== 'link') {
            return false;
        }

        return $material === null || $material->material_type !== 'link' || empty($material->url);
    }
}
