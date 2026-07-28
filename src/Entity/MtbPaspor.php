<?php

declare(strict_types=1);

namespace App\Entity;

use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * @property int $id
 * @property int $kds
 * @property string $no_paspor
 * @property string $tanggal_dikeluarkan
 * @property string $tempat_dikeluarkan
 * @property string $exp_paspor
 * @property int $aktif
 */
final class MtbPaspor extends ActiveRecord
{
    public function tableName(): string
    {
        return 'mtb_paspor';
    }

    public function getSantri()
    {
        return $this->hasOne(MasterSantri::class, ['kds' => 'kds']);
    }
}
