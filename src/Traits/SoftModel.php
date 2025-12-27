<?php

namespace Core\Traits;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SoftModel
{
    /**
     * Soft delete model
     *
     * @param mixed $model - Model for delete
     * @param string $alreadyDeleteMessage - Message if model is already in recycle bin
     * @param string $successMessage - Success message
     * @param string $errorMessage - Error message
     * @param boolean $withTransaction = true - Need use transaction
     * @param boolean $writeErrorLog = true - Need to write error log
     *
     * @return array
     */
    public static function destroyModel(
        $model, string $alreadyDeleteMessage,
        string $successMessage, string $errorMessage,
        bool $withTransaction = true,
        bool $writeErrorLog = true
    ): array {
        if ($model->deleted_at !== null) {
            return [
                'code' => 400,
                'message' => $alreadyDeleteMessage
            ];
        }

        try {
            if ($withTransaction) DB::beginTransaction();

            $model->fireModelEvent('deleted', false);

            if ($withTransaction) DB::commit();

            return [
                'code' => 200,
                'message' => $successMessage
            ];
        } catch (Exception $e) {
            if ($writeErrorLog) config('core.log', Log::class)::error($e);
            if ($withTransaction) DB::rollBack();
            return [
                'code' => 400,
                'message' => $errorMessage
            ];
        }
    }

    /**
     * Restore model
     *
     * @param mixed $model - Model for restore
     * @param string $notDeleteMessage - Message if model is already not in recycle bin
     * @param string $successMessage - Success message
     * @param string $errorMessage -Error message
     * @param boolean $withTransaction = true - Need use transaction
     * @param boolean $writeErrorLog = true - Need to write error log
     *
     * @return array
     */
    public static function restoreModel(
        $model, string $notDeleteMessage,
        string $successMessage, string $errorMessage,
        bool $withTransaction = true,
        bool $writeErrorLog = true
    ): array {
        if ($model->deleted_at === null) {
            return [
                'code' => 400,
                'message' => $notDeleteMessage
            ];
        }

        try {
            if ($withTransaction) DB::beginTransaction();

            $model->fireModelEvent('restored', false);

            if ($withTransaction) DB::commit();

            return [
                'code' => 200,
                'message' => $successMessage
            ];
        } catch (Exception $e) {
            if ($writeErrorLog) config('core.log', Log::class)::error($e);

            if ($withTransaction) DB::rollBack();

            return [
                'code' => 400,
                'message' => $errorMessage
            ];
        }
    }
}
