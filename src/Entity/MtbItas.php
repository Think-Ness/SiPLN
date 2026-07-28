<?php

declare(strict_types=1);

namespace App\Entity;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int $id
 * @property int $kds
 * @property string $no_itas
 * @property string $exp_itas
 * @property int $level_itas
 * @property int $aktif
 * @property string $path_file
 */
final class MtbItas extends ActiveRecord
{
    public function tableName(): string
    {
        return 'mtb_itas';
    }

    public function getSantri()
    {
        return $this->hasOne(MasterSantri::class, ['kds' => 'kds']);
    }
}
